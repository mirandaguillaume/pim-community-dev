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

    public function __construct(private readonly CodeCoverage $coverage)
    {
    }

    /**
     * Production factory: line coverage over src/**, using whatever driver is
     * active (PCOV in the nightly). Only call this when a driver is available.
     */
    public static function create(): self
    {
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

    public function start(): void
    {
        $this->coverage->start('behat');
    }

    public function stopAndDump(string $dir): void
    {
        $this->coverage->stop();

        if (!is_dir($dir)) {
            @mkdir($dir, 0o777, true);
        }

        $file = $dir . '/' . getmypid() . '-' . uniqid('', true) . '.cov';
        (new PhpReport())->process($this->coverage, $file);
    }
}
