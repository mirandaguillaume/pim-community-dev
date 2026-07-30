<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use PHPUnit\Framework\TestCase;

final class BuildPhpInventoryCliTest extends TestCase
{
    private string $dir;
    private string $srcDir;
    private string $covered;
    private string $excluded;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/phpinv-' . uniqid('', true);
        $this->srcDir = $this->dir . '/src';
        mkdir($this->srcDir, 0o775, true);
        $this->covered = $this->srcDir . '/Covered.php';
        $this->excluded = $this->srcDir . '/ThingTest.php';
        file_put_contents($this->covered, "<?php\nfunction c() { return 1; }\n");
        file_put_contents($this->excluded, "<?php\nfunction e() { return 1; }\n");
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

    public function test_it_writes_one_entry_per_test_excluding_test_files_and_foreign_paths(): void
    {
        file_put_contents(
            $this->dir . '/111.dump',
            RawCoverageRecorder::encode([$this->covered => [2 => 1]], 'a.feature:1')
            . RawCoverageRecorder::encode([$this->excluded => [2 => 1]], 'a.feature:1')
            . RawCoverageRecorder::encode(['/somewhere/else/Nope.php' => [2 => 1]], 'a.feature:1')
            . RawCoverageRecorder::encode([$this->covered => [2 => 1]], 'b.feature:9'),
        );
        $out = $this->dir . '/inv.json';

        [$exit] = $this->runCli($out);

        self::assertSame(0, $exit);
        $inv = json_decode((string) file_get_contents($out), true);

        // Paths are repo-relative, test files and out-of-tree paths dropped.
        self::assertSame(['a.feature:1', 'b.feature:9'], array_keys($inv));
        self::assertSame(['src/Covered.php' => [2]], $inv['a.feature:1']);
        self::assertSame(['src/Covered.php' => [2]], $inv['b.feature:9']);
    }

    public function test_it_warns_and_exits_zero_when_there_are_no_dumps(): void
    {
        [$exit, $stderr] = $this->runCli($this->dir . '/inv.json');

        self::assertSame(0, $exit);
        self::assertStringContainsString('WARNING', $stderr);
    }

    /** @return array{0: int, 1: string} */
    private function runCli(string $out): array
    {
        exec(sprintf(
            '%s %s --in %s --src %s --out %s 2>&1',
            escapeshellarg(PHP_BINARY),
            escapeshellarg(__DIR__ . '/build-php-inventory.php'),
            escapeshellarg($this->dir),
            escapeshellarg($this->srcDir),
            escapeshellarg($out),
        ), $output, $exit);

        return [$exit, implode("\n", $output)];
    }
}
