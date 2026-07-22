<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\PHP as PhpReport;

final class CoverageMergerTest extends TestCase
{
    private string $dir;
    private string $fixtureSrc;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/behatcov-' . uniqid('', true);
        mkdir($this->dir, 0o777, true);

        // A fixture source file with executable statements on lines 4 and 6.
        $this->fixtureSrc = $this->dir . '/Fixture.php';
        file_put_contents($this->fixtureSrc, <<<'PHP'
        <?php
        function fixture_target($x)
        {
            $a = $x + 1;
            if ($a > 0) {
                return $a;
            }
            return 0;
        }
        PHP);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->dir);
    }

    public function test_it_merges_cov_files_and_reports_the_union_of_covered_lines(): void
    {
        // dump A: line 4 executed
        $this->dumpCov($this->dir . '/a.cov', [4 => 1]);
        // dump B: line 6 executed
        $this->dumpCov($this->dir . '/b.cov', [6 => 1]);

        $merger = new CoverageMerger();
        $merged = $merger->mergeDir($this->dir);
        self::assertInstanceOf(CodeCoverage::class, $merged);

        $lcovPath = $this->dir . '/lcov.info';
        $merger->writeLcov($merged, $lcovPath);
        $lcov = file_get_contents($lcovPath);

        self::assertStringContainsString('SF:' . $this->fixtureSrc, $lcov);
        // union → both line 4 and line 6 hit at least once (DA:<line>,<count>=1..)
        self::assertMatchesRegularExpression('/^DA:4,[1-9]/m', $lcov);
        self::assertMatchesRegularExpression('/^DA:6,[1-9]/m', $lcov);
    }

    public function test_it_returns_null_when_the_directory_has_no_cov_files(): void
    {
        self::assertNull((new CoverageMerger())->mergeDir($this->dir));
    }

    /** @param array<int,int> $executedLines line => xdebug count (1 = executed) */
    private function dumpCov(string $path, array $executedLines): void
    {
        $filter = new Filter();
        $filter->includeFile($this->fixtureSrc);

        $coverage = new CodeCoverage(new FakeCoverageDriver(), $filter);
        $coverage->append(
            RawCodeCoverageData::fromXdebugWithoutPathCoverage([$this->fixtureSrc => $executedLines]),
            'fixture',
        );

        (new PhpReport())->process($coverage, $path);
    }
}
