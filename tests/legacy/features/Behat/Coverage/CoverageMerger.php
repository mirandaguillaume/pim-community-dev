<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Clover;

/**
 * Merges the per-request serialized CodeCoverage dumps (*.cov, written by
 * {@see CoverageCollector} via Report\PHP) in a directory into a single object
 * and renders lcov/clover. Incremental (load → merge → free) to bound memory.
 *
 * NB: phpunit/php-code-coverage (as vendored, 10.1.16) ships Clover/Cobertura/
 * Crap4j/Html/PHP/Text/Xml report writers but has never shipped a
 * `Report\Lcov` class, so LCOV is rendered by hand from CodeCoverage's own
 * public line-coverage data instead of delegating to a (non-existent) report
 * writer.
 */
final class CoverageMerger
{
    public function mergeDir(string $dir): ?CodeCoverage
    {
        $merged = null;

        foreach (glob(rtrim($dir, '/') . '/*.cov') ?: [] as $file) {
            // Report\PHP dumps `<?php return \unserialize(...);` → include returns the object.
            $coverage = @include $file;
            if (!$coverage instanceof CodeCoverage) {
                continue;
            }

            if ($merged === null) {
                $merged = $coverage;
            } else {
                $merged->merge($coverage);
            }

            unset($coverage);
        }

        return $merged;
    }

    public function writeLcov(CodeCoverage $coverage, string $path): void
    {
        $lines = [];

        foreach ($coverage->getData()->lineCoverage() as $file => $coveredLines) {
            ksort($coveredLines);

            $found = 0;
            $hit = 0;
            $records = [];

            foreach ($coveredLines as $line => $testIds) {
                // null means the line is not executable per static analysis; LCOV DA: only lists executable lines.
                if ($testIds === null) {
                    continue;
                }

                $found++;
                $count = count($testIds);

                if ($count > 0) {
                    $hit++;
                }

                $records[] = sprintf('DA:%d,%d', $line, $count);
            }

            $lines[] = 'TN:';
            $lines[] = 'SF:' . $file;
            array_push($lines, ...$records);
            $lines[] = 'LF:' . $found;
            $lines[] = 'LH:' . $hit;
            $lines[] = 'end_of_record';
        }

        file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL);
    }

    public function writeClover(CodeCoverage $coverage, string $path): void
    {
        (new Clover())->process($coverage, $path);
    }
}
