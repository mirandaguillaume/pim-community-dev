<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

/**
 * The current-test marker: how a test runner tells the server-side collector which test caused a
 * request.
 *
 * A file rather than a cookie, deliberately. A cookie only attributes requests the browser makes,
 * and setting one through Selenium before the first navigation raises `invalid cookie domain`. A
 * file also works for the Playwright suite, whose PHP requests come from a different browser stack.
 *
 * Safe as a single global file because scenarios run SEQUENTIALLY within a shard and each shard is
 * its own container and workspace, so there is never more than one writer.
 */
final class TestMarker
{
    private const FILENAME = '.current-test';

    public static function write(string $dir, string $testId): void
    {
        if (!\is_dir($dir)) {
            @\mkdir($dir, 0o775, true);
        }

        @\file_put_contents($dir . '/' . self::FILENAME, $testId);
    }

    /**
     * Returns '' when there is no marker. The shim calls this on every request, including before any
     * scenario has begun, so absence is normal and must be quiet.
     */
    public static function read(string $dir): string
    {
        $raw = @\file_get_contents($dir . '/' . self::FILENAME);

        return $raw === false ? '' : \trim($raw);
    }
}
