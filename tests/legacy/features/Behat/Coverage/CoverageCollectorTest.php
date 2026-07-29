<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use PHPUnit\Framework\TestCase;

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

    public function test_it_appends_a_decodable_record_named_after_the_worker_pid(): void
    {
        $collector = new CoverageCollector(static fn (): array => [
            '/srv/pim/src/A.php' => [3 => 1, 4 => 0],
        ]);

        $collector->start();          // inert without PCOV
        $collector->stopAndDump($this->dir); // creates the dir if missing

        $expected = $this->dir . '/' . getmypid() . '.dump';
        self::assertFileExists($expected);

        self::assertSame(
            [['/srv/pim/src/A.php' => [3 => 1]]],
            RawCoverageRecorder::decodeAll((string) file_get_contents($expected)),
        );
    }

    public function test_successive_requests_in_one_worker_append_to_the_same_file(): void
    {
        $maps = [
            ['/srv/pim/src/A.php' => [3 => 1]],
            ['/srv/pim/src/B.php' => [9 => 1]],
        ];
        $collector = new CoverageCollector(static function () use (&$maps): array {
            return array_shift($maps) ?? [];
        });

        $collector->stopAndDump($this->dir);
        $collector->stopAndDump($this->dir);

        self::assertCount(1, glob($this->dir . '/*.dump') ?: []);
        self::assertSame(
            [
                ['/srv/pim/src/A.php' => [3 => 1]],
                ['/srv/pim/src/B.php' => [9 => 1]],
            ],
            RawCoverageRecorder::decodeAll((string) file_get_contents($this->dir . '/' . getmypid() . '.dump')),
        );
    }

    public function test_it_writes_nothing_when_no_line_was_executed(): void
    {
        // A request that touches no src/ code must not leave an empty record for the merge to read.
        $collector = new CoverageCollector(static fn (): array => ['/srv/pim/src/A.php' => [3 => 0]]);

        $collector->stopAndDump($this->dir);

        self::assertSame([], glob($this->dir . '/*.dump') ?: []);
    }

    public function test_it_is_inert_when_pcov_is_not_collecting(): void
    {
        // Covers the real production factory and the real PCOV path, which must degrade quietly
        // rather than fatal or throw.
        //
        // Deliberately does NOT assert on extension_loaded('pcov'): the answer differs by
        // environment. The CI image installs php-pcov with pcov.enabled=0 (Dockerfile:80,
        // docker/build/pcov.ini), so the extension IS loaded there and merely disabled, while a
        // stale local image may not have it at all. Both cases must behave identically — nothing
        // was collected, so nothing is written — and asserting the environment instead of the
        // behaviour would make this test pass locally and fail in CI.
        $collector = CoverageCollector::create();
        $collector->start();
        $collector->stopAndDump($this->dir);

        self::assertSame([], glob($this->dir . '/*.dump') ?: []);
    }
}
