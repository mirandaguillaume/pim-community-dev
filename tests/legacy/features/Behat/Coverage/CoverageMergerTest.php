<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use PHPUnit\Framework\TestCase;

final class CoverageMergerTest extends TestCase
{
    private string $dir;
    private string $srcDir;
    private string $covered;
    private string $untouched;
    private string $excluded;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/behatcov-' . uniqid('', true);
        $this->srcDir = $this->dir . '/src';
        mkdir($this->srcDir, 0o777, true);

        $body = <<<'PHP'
        <?php
        function fixture_target($x)
        {
            $a = $x + 1;
            if ($a > 0) {
                return $a;
            }
            return 0;
        }
        PHP;

        // Executable statements sit on lines 4, 5, 6 and 8 -- verified against ParsingFileAnalyser,
        // which is what decides the denominator. Four statements in total.
        $this->covered = $this->srcDir . '/Covered.php';
        $this->untouched = $this->srcDir . '/Untouched.php';
        $this->excluded = $this->srcDir . '/ThingTest.php';

        file_put_contents($this->covered, $body);
        file_put_contents($this->untouched, str_replace('fixture_target', 'untouched_target', $body));
        file_put_contents($this->excluded, str_replace('fixture_target', 'excluded_target', $body));
    }

    protected function tearDown(): void
    {
        foreach (glob($this->srcDir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        foreach (glob($this->dir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->srcDir);
        @rmdir($this->dir);
    }

    public function test_it_unions_records_across_several_worker_dumps(): void
    {
        file_put_contents(
            $this->dir . '/111.dump',
            RawCoverageRecorder::encode([$this->covered => [4 => 1]], 't:1'),
        );
        file_put_contents(
            $this->dir . '/222.dump',
            RawCoverageRecorder::encode([$this->covered => [6 => 1]], 't:1')
            . RawCoverageRecorder::encode([$this->untouched => [4 => 1]], 't:1'),
        );

        self::assertSame(
            [
                $this->covered => [4 => 1, 6 => 1],
                $this->untouched => [4 => 1],
            ],
            (new CoverageMerger())->unionDir($this->dir),
        );
    }

    public function test_union_dir_is_empty_when_there_are_no_dumps(): void
    {
        self::assertSame([], (new CoverageMerger())->unionDir($this->dir));
    }

    public function test_the_filter_includes_sources_and_excludes_test_suffixes(): void
    {
        $files = (new CoverageMerger())->sourceFilter($this->srcDir)->files();

        self::assertContains($this->covered, $files);
        self::assertContains($this->untouched, $files);
        self::assertNotContains($this->excluded, $files, 'ThingTest.php must not inflate the denominator');
    }

    public function test_untouched_sources_still_count_towards_the_denominator(): void
    {
        // The whole point of keeping a Filter at merge time: a file no request ever hit must appear
        // as 0%, not vanish. Without it the report degenerates towards a falsely high percentage --
        // the failure #343 fixed on the Playwright side.
        //
        // This covers UNTOUCHED files only, and asserts paths rather than counts. The unhit lines of
        // a touched file are a separate mechanism and a separate test:
        // test_a_partially_covered_file_keeps_its_unhit_executable_lines.
        $merger = new CoverageMerger();
        $filter = $merger->sourceFilter($this->srcDir);
        $coverage = $merger->toCodeCoverage([$this->covered => [4 => 1]], $filter, null);

        $clover = $this->dir . '/clover.xml';
        $merger->writeClover($coverage, $clover);

        $xml = (string) file_get_contents($clover);
        self::assertStringContainsString($this->covered, $xml);
        self::assertStringContainsString($this->untouched, $xml);
        self::assertStringNotContainsString($this->excluded, $xml);
    }

    public function test_a_partially_covered_file_keeps_its_unhit_executable_lines(): void
    {
        // Covered.php has executable lines 4, 5, 6 and 8. Hitting only line 4 must read 1/4, not 1/1.
        // Without the merge-side skeleton backfill the denominator collapses to the hit set and every
        // partially-covered file reports 100% — which, in an E2E run, is almost every touched file.
        $merger = new CoverageMerger();
        $coverage = $merger->toCodeCoverage(
            [$this->covered => [4 => 1]],
            $merger->sourceFilter($this->srcDir),
            null,
        );
        $merger->writeClover($coverage, $clover = $this->dir . '/clover.xml');

        $xml = new \SimpleXMLElement((string) file_get_contents($clover));
        $file = $xml->xpath(sprintf('//file[@name="%s"]', $this->covered))[0];
        self::assertSame('4', (string) $file->metrics['statements']);
        self::assertSame('1', (string) $file->metrics['coveredstatements']);
    }

    public function test_it_reports_the_union_as_covered_lines(): void
    {
        $merger = new CoverageMerger();
        $coverage = $merger->toCodeCoverage(
            [$this->covered => [4 => 1, 6 => 1]],
            $merger->sourceFilter($this->srcDir),
            null,
        );

        $lineCoverage = $coverage->getData()->lineCoverage();
        self::assertArrayHasKey($this->covered, $lineCoverage);
        self::assertNotEmpty($lineCoverage[$this->covered][4] ?? []);
        self::assertNotEmpty($lineCoverage[$this->covered][6] ?? []);
    }

    public function test_static_analysis_cache_directory_is_used_when_given(): void
    {
        $cache = $this->dir . '/sa-cache';
        $merger = new CoverageMerger();
        $coverage = $merger->toCodeCoverage(
            [$this->covered => [4 => 1]],
            $merger->sourceFilter($this->srcDir),
            $cache,
        );
        $merger->writeClover($coverage, $this->dir . '/clover.xml');

        self::assertTrue($coverage->cachesStaticAnalysis());
        self::assertDirectoryExists($cache);
        self::assertNotSame([], glob($cache . '/*') ?: [], 'the analyser should have written cache entries');

        // One entry per analysed file and no more: Covered.php (touched, so the backfill analyses it
        // and append() then re-reads it) and Untouched.php (added by addUncoveredFilesFromFilter()
        // during writeClover()). ThingTest.php is filtered out and never parsed.
        //
        // This is the guard on backfillExecutableLines()'s (true, false) flags. CachingFileAnalyser
        // keys its cache on both (CachingFileAnalyser.php:147-161), so if the backfill's analyser
        // ever stops matching CodeCoverage's own defaults, Covered.php lands under two different keys
        // and this count becomes 3 -- the silent double-parse the flags exist to prevent.
        self::assertCount(
            2,
            glob($cache . '/*') ?: [],
            'the backfill and append() must share cache entries, not write a set each',
        );

        foreach (glob($cache . '/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($cache);
    }
}
