<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use PHPUnit\Framework\TestCase;

final class MergeCliTest extends TestCase
{
    private string $dir;
    private string $srcDir;
    private string $covered;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/behatcli-' . uniqid('', true);
        $this->srcDir = $this->dir . '/src';
        mkdir($this->srcDir, 0o777, true);

        $this->covered = $this->srcDir . '/Covered.php';
        file_put_contents($this->covered, <<<'PHP'
        <?php
        function cli_target($x)
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
        foreach (glob($this->srcDir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        // Clean up report/ subdirectory created during tests (unlink only removes files, not dirs)
        foreach (glob($this->dir . '/report/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->dir . '/report');
        // Then clean the top-level dir
        foreach (glob($this->dir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->srcDir);
        @rmdir($this->dir);
    }

    public function test_it_writes_clover_and_exits_zero_for_real_dumps(): void
    {
        file_put_contents(
            $this->dir . '/111.dump',
            RawCoverageRecorder::encode([$this->covered => [4 => 1]]),
        );
        $clover = $this->dir . '/report/clover.xml';

        [$exit, $stderr] = $this->runCli($clover);

        self::assertSame(0, $exit);
        self::assertFileExists($clover);
        self::assertStringNotContainsString('WARNING', $stderr);
    }

    public function test_it_warns_and_still_exits_zero_when_there_are_no_dumps(): void
    {
        // The signature of PCOV not being active in the fpm SAPI. Must be loud but must never fail
        // the nightly job.
        [$exit, $stderr] = $this->runCli($this->dir . '/report/clover.xml');

        self::assertSame(0, $exit);
        self::assertStringContainsString('WARNING', $stderr);
        self::assertStringContainsString('0 records', $stderr);
    }

    public function test_it_warns_when_the_merge_covers_zero_lines(): void
    {
        // Dumps exist but every path in them falls outside the filter -- e.g. a source-path mismatch
        // between the container and the merge. Exit code alone would call that a success.
        file_put_contents(
            $this->dir . '/111.dump',
            RawCoverageRecorder::encode(['/somewhere/else/Nope.php' => [4 => 1]]),
        );

        [$exit, $stderr] = $this->runCli($this->dir . '/report/clover.xml');

        self::assertSame(0, $exit);
        self::assertStringContainsString('WARNING', $stderr);
        self::assertStringContainsString('0 covered lines', $stderr);
    }

    public function test_a_non_executable_line_does_not_count_as_covered(): void
    {
        // Regression guard for the null-vs-empty-array distinction. The dump names ONLY line 1
        // (`<?php`), which the static analyser marks non-executable, in a file that DOES clear the
        // --src filter. So the file survives applyFilter() and reaches the counting loop — unlike
        // the out-of-filter case above, where the file is dropped before the loop runs and the bug
        // cannot manifest.
        //
        // With the buggy `$tests !== []` guard, every non-executable line in this file counts as
        // covered, $coveredLines lands well above zero and the tripwire stays silent. This test is
        // the only thing in the suite that fails in that state.
        file_put_contents(
            $this->dir . '/111.dump',
            RawCoverageRecorder::encode([$this->covered => [1 => 1]]),
        );

        [$exit, $stderr] = $this->runCli($this->dir . '/report/clover.xml');

        self::assertSame(0, $exit);
        self::assertStringContainsString('WARNING', $stderr);
        self::assertStringContainsString('0 covered lines', $stderr);
    }

    /** @return array{0: int, 1: string} */
    private function runCli(string $clover): array
    {
        $script = __DIR__ . '/merge-behat-coverage.php';
        $cmd = sprintf(
            '%s %s --in=%s --clover=%s --src=%s 2>&1',
            escapeshellarg(PHP_BINARY),
            escapeshellarg($script),
            escapeshellarg($this->dir),
            escapeshellarg($clover),
            escapeshellarg($this->srcDir),
        );

        exec($cmd, $output, $exit);

        return [$exit, implode("\n", $output)];
    }
}
