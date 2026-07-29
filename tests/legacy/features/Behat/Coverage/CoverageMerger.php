<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\Clover;
use SebastianBergmann\CodeCoverage\StaticAnalysis\CachingFileAnalyser;
use SebastianBergmann\CodeCoverage\StaticAnalysis\FileAnalyser;
use SebastianBergmann\CodeCoverage\StaticAnalysis\ParsingFileAnalyser;

/**
 * Turns the per-request hit-line dumps into one Clover report.
 *
 * This is where every expensive step lives, on purpose: building the src/** Filter, constructing a
 * CodeCoverage, and running php-code-coverage's static analysis to resolve executable lines. Each
 * happens ONCE per shard here instead of once per HTTP request in the collector, which is the whole
 * point of the rework.
 */
final class CoverageMerger
{
    /**
     * Union every complete record in every worker dump in a directory.
     *
     * @return array<string, array<int, int>>
     */
    public function unionDir(string $dir): array
    {
        $union = [];

        foreach (\glob(\rtrim($dir, '/') . '/*.dump') ?: [] as $file) {
            $blob = @\file_get_contents($file);

            if ($blob === false) {
                continue;
            }

            foreach (RawCoverageRecorder::decodeAll($blob) as $record) {
                $union = RawCoverageRecorder::union($union, $record);
            }
        }

        return $union;
    }

    /**
     * The coverage allowlist, which is also the denominator.
     *
     * Mirrors phpunit.xml.dist's <source> excludes so the e2e-behat flag measures the same body of
     * code as the backend flag. Excluding test classes matters because they run at ~100% and would
     * otherwise flatter the number.
     */
    public function sourceFilter(string $srcDir): Filter
    {
        $filter = new Filter();
        $filter->includeDirectory($srcDir);

        foreach ($filter->files() as $file) {
            if (\str_ends_with($file, 'Test.php')
                || \str_ends_with($file, 'Integration.php')
                || \str_ends_with($file, 'EndToEnd.php')
            ) {
                $filter->excludeFile($file);
            }
        }

        return $filter;
    }

    /**
     * @param array<string, array<int, int>> $union
     * @param string|null                    $cacheDir static-analysis cache; null disables caching
     */
    public function toCodeCoverage(array $union, Filter $filter, ?string $cacheDir): CodeCoverage
    {
        // FakeCoverageDriver rather than a real driver: nothing is ever started here, the raw data
        // is handed straight to append(). That also lets the merge run in a container without PCOV.
        $coverage = new CodeCoverage(new FakeCoverageDriver(), $filter);

        if ($cacheDir !== null) {
            if (!\is_dir($cacheDir)) {
                // 0o775, not 0o777: same reasoning as the collector's dump dir. Everything that reads
                // or writes this cache runs in the same container and group.
                @\mkdir($cacheDir, 0o775, true);
            }

            // Without this, append() resolves executable lines through a bare ParsingFileAnalyser
            // (CodeCoverage.php:609) and re-parses every covered file on every run.
            $coverage->cacheStaticAnalysis($cacheDir);
        }

        $union = $this->backfillExecutableLines($union, $filter, $cacheDir);

        // The single append. Together with the backfill above it is the whole of the static analysis:
        // both share one CachingFileAnalyser cache, so each file is parsed at most once per shard.
        $coverage->append(RawCodeCoverageData::fromXdebugWithoutPathCoverage($union), 'behat');

        return $coverage;
    }

    /**
     * Restore the executable-line skeleton that the recorder dropped.
     *
     * RawCoverageRecorder::reduce() keeps hits only, so a file some request touched arrives here
     * carrying nothing but hit lines. append() would then build that file's denominator from those
     * keys alone and render it at exactly 100%. CodeCoverage's own rescue, addUncoveredFilesFromFilter()
     * (CodeCoverage.php:476), does not help: it diffs the Filter against the ALREADY-COVERED files, so
     * it only ever rescues files with zero hits. In an E2E run nearly every touched file is partially
     * covered, which is precisely the population that would be misreported.
     *
     * The alternative -- shipping the -1 markers from every request -- is equally correct but inflates
     * the dump volume and pushes parse work back into the request path, the two things this rework
     * exists to avoid. Doing it here costs one extra analyser pass per touched file, cached.
     *
     * @param array<string, array<int, int>> $union
     *
     * @return array<string, array<int, int>>
     */
    private function backfillExecutableLines(array $union, Filter $filter, ?string $cacheDir): array
    {
        $analyser = $this->fileAnalyser($cacheDir);

        foreach ($union as $file => $hits) {
            // isExcluded() is the same allowlist gate append() applies (CodeCoverage.php:422), so the
            // backfill tracks exactly the files that will survive into the report -- and because it
            // delegates to isFile(), it also keeps runtime-evaluated pseudo-files (Filter.php:92-100)
            // and files that have since vanished from disk out of the parser.
            if ($filter->isExcluded($file)) {
                continue;
            }

            $skeleton = RawCodeCoverageData::fromUncoveredFile($file, $analyser)->lineCoverage()[$file] ?? [];

            // `+` on int-keyed arrays keeps the LEFT operand for duplicate keys, so a hit (1) wins
            // over the skeleton's LINE_NOT_EXECUTED (-1). array_merge would renumber the integer keys
            // and destroy the line numbers outright.
            $union[$file] = $hits + $skeleton;
        }

        return $union;
    }

    /**
     * `true, false` are CodeCoverage's own defaults for useAnnotationsForIgnoringCode and
     * ignoreDeprecatedCode (CodeCoverage.php:53,57), and both constructors below must be given the
     * same pair for two different reasons:
     *
     * - on CachingFileAnalyser they are part of the cache KEY (CachingFileAnalyser.php:147-161).
     *   Diverging from what CodeCoverage::analyser() passes means every lookup misses, so each file
     *   is parsed once for the backfill and again for append() -- silently, at twice the cost.
     *   test_static_analysis_cache_directory_is_used_when_given pins the entry count for this.
     * - on the wrapped ParsingFileAnalyser they decide the analysis SEMANTICS (whether
     *   @codeCoverageIgnore annotations are honoured). They do not reach the cache key at all, so a
     *   mismatch between the two constructors would be worse than a cache miss: results computed
     *   under one set of flags would be stored under a key claiming the other.
     */
    private function fileAnalyser(?string $cacheDir): FileAnalyser
    {
        $analyser = new ParsingFileAnalyser(true, false);

        return $cacheDir !== null
            ? new CachingFileAnalyser($cacheDir, $analyser, true, false)
            : $analyser;
    }

    public function writeClover(CodeCoverage $coverage, string $path): void
    {
        (new Clover())->process($coverage, $path);
    }
}
