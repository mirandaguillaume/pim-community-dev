<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter;

final class CoverageCollectorTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/behatcoll-' . uniqid('', true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->dir);
    }

    public function test_stop_and_dump_writes_a_uniquely_named_loadable_cov_file(): void
    {
        $collector = new CoverageCollector(new CodeCoverage(new FakeCoverageDriver(), new Filter()));

        $collector->start();
        $collector->stopAndDump($this->dir); // creates the dir if missing

        $files = glob($this->dir . '/*.cov') ?: [];
        self::assertCount(1, $files);
        self::assertMatchesRegularExpression('/\/\d+-[0-9a-f.]+\.cov$/', $files[0]);

        $loaded = include $files[0];
        self::assertInstanceOf(CodeCoverage::class, $loaded);
    }
}
