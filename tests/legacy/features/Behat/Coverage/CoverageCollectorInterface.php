<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

/**
 * Collects PHP line coverage for a single HTTP request and dumps it to disk.
 * Extracted so the subscriber can be unit-tested with a spy (the real
 * collector needs a live PCOV driver, only present in the nightly).
 */
interface CoverageCollectorInterface
{
    public function start(): void;

    public function stopAndDump(string $dir): void;
}
