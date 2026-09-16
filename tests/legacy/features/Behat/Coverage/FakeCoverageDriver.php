<?php

declare(strict_types=1);

namespace Pim\Behat\Coverage;

use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;

/**
 * A driver-free {@see Driver} so tests can build real CodeCoverage objects
 * without PCOV/Xdebug loaded. start()/stop() are inert; synthetic coverage is
 * injected via CodeCoverage::append() in the tests, not via this driver.
 */
final class FakeCoverageDriver extends Driver
{
    public function nameAndVersion(): string
    {
        return 'FakeCoverageDriver 1.0';
    }

    public function start(): void
    {
    }

    public function stop(): RawCodeCoverageData
    {
        return RawCodeCoverageData::fromXdebugWithoutPathCoverage([]);
    }
}
