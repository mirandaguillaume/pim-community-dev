<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

/**
 * Records PCOV line coverage for one HTTP request and appends it to a per-worker dump file.
 *
 * Deliberately holds NO php-code-coverage object. The previous implementation built a CodeCoverage
 * per request and called stop() on it, which runs append() -> applyExecutableLinesFilter() ->
 * ParsingFileAnalyser: a nikic/php-parser parse of every file the request touched, uncached because
 * cacheStaticAnalysis() was never called. Measured on the nightly suite that cost 38.4 min/shard
 * against a 7.6 min baseline. With the collector reduced to PCOV's own lifecycle, a shard ran
 * 7.5 min -- i.e. PCOV itself is free here and the whole overhead was that userland work
 * (run 30453503181 vs 30425913943, 2026-07-29).
 *
 * Every fpm worker writes its own <pid>.dump, so records never interleave between processes, and
 * requests within a worker are sequential -- appends need no locking.
 */
final class CoverageCollector implements CoverageCollectorInterface
{
    /**
     * Returns a raw PCOV map, array<string $file, array<int $line, int $hits>>.
     *
     * Injectable because ext-pcov is a runtime-only extension: it is absent from every dev checkout
     * and from the unit-test job, so the write path would otherwise be untestable.
     *
     * @var (callable(): array<string, array<int, int>>)|null
     */
    private $collect;

    /**
     * @param (callable(): array<string, array<int, int>>)|null $collect
     */
    public function __construct(?callable $collect = null)
    {
        $this->collect = $collect;
    }

    public static function create(): self
    {
        return new self();
    }

    public function start(): void
    {
        self::pcov('start');
    }

    public function stopAndDump(string $dir): void
    {
        $raw = $this->collect !== null ? ($this->collect)() : self::collectFromPcov();
        $hits = RawCoverageRecorder::reduce($raw);

        if ($hits === []) {
            return; // a request that executed no src/ line leaves no record to merge
        }

        if (!\is_dir($dir)) {
            @\mkdir($dir, 0o777, true);
        }

        @\file_put_contents(
            $dir . '/' . \getmypid() . '.dump',
            RawCoverageRecorder::encode($hits),
            \FILE_APPEND,
        );
    }

    /**
     * @return array<string, array<int, int>>
     */
    private static function collectFromPcov(): array
    {
        self::pcov('stop');

        /** @var list<string>|null $waiting */
        $waiting = self::pcov('waiting');

        if (!\is_array($waiting) || $waiting === []) {
            return [];
        }

        // No intersect against a src/** Filter here: pcov.directory=/srv/pim/src (docker/build/pcov.ini)
        // already scopes collection in C, and the previous userland array_intersect against a Filter
        // holding every path under src/ ran on every single request. Test-file exclusion is applied
        // once, at merge time, by CoverageMerger::sourceFilter().
        $inclusive = \defined('pcov\inclusive') ? \constant('pcov\inclusive') : 1;

        /** @var array<string, array<int, int>>|null $raw */
        $raw = self::pcov('collect', [$inclusive, $waiting]);

        self::pcov('clear');

        return \is_array($raw) ? $raw : [];
    }

    /**
     * Call a `pcov\*` function through a variable so neither PHPStan nor the IDE flags it as
     * undefined -- PCOV is a runtime-only extension, absent from dev checkouts and from the image on
     * non-coverage runs. function_exists() makes every call a no-op when PCOV is missing, which
     * matters because this class and the subscriber's gate can in principle disagree.
     *
     * @param list<mixed> $args
     */
    private static function pcov(string $function, array $args = []): mixed
    {
        $callable = '\pcov\\' . $function;

        return \function_exists($callable) ? $callable(...$args) : null;
    }
}
