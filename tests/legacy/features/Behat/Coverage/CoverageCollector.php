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
    public function __construct(private readonly CodeCoverage $coverage)
    {
    }

    /**
     * Production factory: line coverage over src/**, using whatever driver is
     * active (PCOV in the nightly). Only call this when a driver is available.
     */
    public static function create(): self
    {
        $filter = new Filter();
        $filter->includeDirectory('/srv/pim/src'); // single recursive scan of src

        // Exclude test classes in-memory (mirrors phpunit.xml.dist <source> excludes)
        // instead of 3 more recursive scans — create() runs once per HTTP request.
        foreach ($filter->files() as $file) {
            if (\str_ends_with($file, 'Test.php')
                || \str_ends_with($file, 'Integration.php')
                || \str_ends_with($file, 'EndToEnd.php')
            ) {
                $filter->excludeFile($file);
            }
        }

        return new self(new CodeCoverage((new Selector())->forLineCoverage($filter), $filter));
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
