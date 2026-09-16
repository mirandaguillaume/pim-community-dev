<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use PHPUnit\Framework\TestCase;

final class TestMarkerTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/marker-' . uniqid('', true);
    }

    protected function tearDown(): void
    {
        @unlink($this->dir . '/.current-test');
        @rmdir($this->dir);
    }

    public function test_it_round_trips_a_test_id(): void
    {
        TestMarker::write($this->dir, 'features/pim/foo.feature:23');

        self::assertSame('features/pim/foo.feature:23', TestMarker::read($this->dir));
    }

    public function test_reading_an_absent_marker_yields_an_empty_string(): void
    {
        // The shim runs on EVERY request, including before any scenario has started and on
        // requests from a suite that never writes a marker. That must be quiet, not fatal.
        self::assertSame('', TestMarker::read($this->dir));
    }

    public function test_write_creates_the_directory_and_overwrites_a_previous_id(): void
    {
        TestMarker::write($this->dir, 'first:1');
        TestMarker::write($this->dir, 'second:2');

        self::assertSame('second:2', TestMarker::read($this->dir));
    }

    public function test_it_trims_trailing_whitespace(): void
    {
        // Guards against a marker written by a shell `echo` in some future caller.
        file_put_contents($this->markerPath(), "third:3\n");

        self::assertSame('third:3', TestMarker::read($this->dir));
    }

    private function markerPath(): string
    {
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0o775, true);
        }

        return $this->dir . '/.current-test';
    }
}
