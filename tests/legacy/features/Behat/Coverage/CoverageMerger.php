<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Clover;

/**
 * Merges the per-request serialized CodeCoverage dumps (*.cov, written by
 * {@see CoverageCollector} via Report\PHP) in a directory into a single object
 * and renders Clover (php-code-coverage 10.1.16 ships no lcov writer; Codecov
 * ingests Clover natively). Incremental (load → merge → free) to bound memory.
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

    public function writeClover(CodeCoverage $coverage, string $path): void
    {
        (new Clover())->process($coverage, $path);
    }
}
