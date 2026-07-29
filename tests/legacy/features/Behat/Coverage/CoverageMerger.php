<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\Clover;

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
                @\mkdir($cacheDir, 0o777, true);
            }

            // Without this, append() resolves executable lines through a bare ParsingFileAnalyser
            // (CodeCoverage.php:609) and re-parses every covered file on every run.
            $coverage->cacheStaticAnalysis($cacheDir);
        }

        // The single append: this is the one place static analysis runs.
        $coverage->append(RawCodeCoverageData::fromXdebugWithoutPathCoverage($union), 'behat');

        return $coverage;
    }

    public function writeClover(CodeCoverage $coverage, string $path): void
    {
        (new Clover())->process($coverage, $path);
    }
}
