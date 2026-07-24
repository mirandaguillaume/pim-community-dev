<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

/**
 * Test double for {@see CoverageCollectorInterface}: records start() calls so
 * BehatCoverageSubscriber's gate can be asserted without a live PCOV driver.
 */
final class SpyCollector implements CoverageCollectorInterface
{
    public int $startCalls = 0;

    public function start(): void
    {
        $this->startCalls++;
    }

    public function stopAndDump(string $dir): void
    {
    }
}
