<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\PHP as PhpReport;

/**
 * Wraps a CodeCoverage for one HTTP request and serializes it to a unique
 * per-request .cov (Report\PHP format) so many fpm workers never collide.
 */
final class CoverageCollector implements CoverageCollectorInterface
{
    /**
     * The src/** line-coverage allowlist, built once per php-fpm worker and cached across the requests
     * that worker serves. Building it recursively scans the whole src/ tree; doing that on every HTTP
     * request (the original behaviour) added enough fixed latency to time out timing-sensitive behat
     * scenarios (select2 searches spinning past the 40s limit) once PCOV was enabled in the nightly.
     * fpm workers are reused across requests, so one scan per worker (dozens) replaces one per request
     * (thousands).
     */
    private static ?Filter $filter = null;

    /**
     * GATE 0a EXPERIMENT — THROWAWAY, revert before the real rework lands.
     *
     * When this marker file exists, the collector does NO php-code-coverage work and NO I/O at all:
     * it only drives PCOV's own start/stop/clear. A behat shard run in that mode therefore measures
     * PCOV's raw instrumentation cost and nothing else, which is the one number that decides whether
     * the raw-collect rework is worth building:
     *
     *   still ~5x vs the 7.6min PCOV-off baseline  => the cost is PCOV-native and irreducible; stop.
     *   close to baseline                          => the cost is ours (per-request append/serialize)
     *                                                 and the rework is the right fix.
     *
     * A marker file rather than an env var on purpose: php-fpm's pool runs with `clear_env = YES`,
     * so container environment variables never reach PHP under the fpm SAPI. The repo is bind-mounted
     * at /srv/pim, so a file touched on the runner is visible to the fpm workers immediately.
     */
    private const NOOP_MARKER = '/srv/pim/var/behat-coverage-noop';

    public function __construct(private readonly ?CodeCoverage $coverage)
    {
    }

    /**
     * Production factory: line coverage over src/**, using whatever driver is
     * active (PCOV in the nightly). Only call this when a driver is available.
     */
    public static function create(): self
    {
        if (\is_file(self::NOOP_MARKER)) {
            return new self(null); // GATE 0a: PCOV lifecycle only, zero userland work
        }

        $filter = self::filter();

        return new self(new CodeCoverage((new Selector())->forLineCoverage($filter), $filter));
    }

    /**
     * Build (once) and return the cached src/** allowlist. The recursive src/ scan + test-file
     * exclusion is the expensive part of coverage setup, so it must run once per process, never per
     * request — see self::$filter.
     */
    private static function filter(): Filter
    {
        if (self::$filter instanceof Filter) {
            return self::$filter;
        }

        $filter = new Filter();
        $filter->includeDirectory('/srv/pim/src'); // single recursive scan of src, cached below

        // Exclude test classes in-memory (mirrors phpunit.xml.dist <source> excludes).
        foreach ($filter->files() as $file) {
            if (\str_ends_with($file, 'Test.php')
                || \str_ends_with($file, 'Integration.php')
                || \str_ends_with($file, 'EndToEnd.php')
            ) {
                $filter->excludeFile($file);
            }
        }

        return self::$filter = $filter;
    }

    /**
     * GATE 0a helper. Calls a `pcov\*` function through a variable so neither PHPStan nor the IDE
     * flags it as undefined: PCOV is a runtime-only extension, absent from every dev checkout and
     * from the image on non-coverage runs. `function_exists` keeps it a no-op when PCOV is absent,
     * which matters because the subscriber's gate and this class can in principle disagree.
     */
    private static function pcov(string $function): void
    {
        $callable = '\pcov\\' . $function;

        if (\function_exists($callable)) {
            $callable();
        }
    }

    public function start(): void
    {
        if ($this->coverage === null) {
            self::pcov('start'); // GATE 0a

            return;
        }

        $this->coverage->start('behat');
    }

    public function stopAndDump(string $dir): void
    {
        if ($this->coverage === null) {
            // GATE 0a. stop()+clear() are C-level and kept deliberately: skipping them would let
            // PCOV's per-process arena grow unbounded across the requests an fpm worker serves,
            // and a worker dying on memory would confound the very measurement being taken.
            self::pcov('stop');
            self::pcov('clear');

            return;
        }

        $this->coverage->stop();

        if (!is_dir($dir)) {
            @mkdir($dir, 0o777, true);
        }

        $file = $dir . '/' . getmypid() . '-' . uniqid('', true) . '.cov';
        (new PhpReport())->process($this->coverage, $file);
    }
}
