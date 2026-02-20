<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Infrastructure\Clock;

use Psr\Clock\ClockInterface;

/**
 * Simple PSR-20 system clock returning the current UTC time.
 * Replaces Lcobucci\Clock\SystemClock which was removed as a transitive dependency
 * when upgrading lcobucci/jwt from 4.x to 5.x.
 */
final class SystemClock implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}
