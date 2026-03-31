<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use PHPUnit\Framework\TestCase;

class BatchStatusTest extends TestCase
{
    private BatchStatus $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_displayable(): void
    {
        $this->sut = new BatchStatus(BatchStatus::ABANDONED);
        $this->assertSame('ABANDONED', $this->sut->__toString());
    }

    public function test_it_is_updatable(): void
    {
        $this->sut = new BatchStatus(BatchStatus::UNKNOWN);
        $this->assertSame('UNKNOWN', $this->sut->__toString());
        $this->sut->setValue(BatchStatus::ABANDONED);
        $this->assertSame('ABANDONED', $this->sut->__toString());
    }

    public function test_it_returns_largest_of_two_values(): void
    {
        $this->assertSame(BatchStatus::FAILED, BatchStatus::max(BatchStatus::FAILED, BatchStatus::COMPLETED));
        $this->assertSame(BatchStatus::FAILED, BatchStatus::max(BatchStatus::COMPLETED, BatchStatus::FAILED));
        $this->assertSame(BatchStatus::FAILED, BatchStatus::max(BatchStatus::FAILED, BatchStatus::FAILED));
        $this->assertSame(BatchStatus::STARTED, BatchStatus::max(BatchStatus::STARTED, BatchStatus::STARTING));
        $this->assertSame(BatchStatus::STARTED, BatchStatus::max(BatchStatus::COMPLETED, BatchStatus::STARTED));
    }

    public function test_it_upgrades_finished_value_when_already_failed(): void
    {
        $this->sut = new BatchStatus(BatchStatus::FAILED);
        $this->sut->upgradeTo(BatchStatus::COMPLETED);
        $this->assertSame('FAILED', $this->sut->__toString());
    }

    public function test_it_upgrades_finished_value_when_already_completed(): void
    {
        $this->sut = new BatchStatus(BatchStatus::COMPLETED);
        $this->sut->upgradeTo(BatchStatus::FAILED);
        $this->assertSame('FAILED', $this->sut->__toString());
    }

    public function test_it_upgrades_unfinished_value_when_starting(): void
    {
        $this->sut = new BatchStatus(BatchStatus::STARTING);
        $this->sut->upgradeTo(BatchStatus::COMPLETED);
        $this->assertSame('COMPLETED', $this->sut->__toString());
    }

    public function test_it_upgrades_unfinished_value_when_completed(): void
    {
        $this->sut = new BatchStatus(BatchStatus::COMPLETED);
        $this->sut->upgradeTo(BatchStatus::STARTING);
        $this->assertSame('COMPLETED', $this->sut->__toString());
    }

    public function test_it_is_not_running_when_failed(): void
    {
        $this->sut = new BatchStatus(BatchStatus::FAILED);
        $this->assertSame(false, $this->sut->isRunning());
    }

    public function test_it_is_not_running_when_completed(): void
    {
        $this->sut = new BatchStatus(BatchStatus::COMPLETED);
        $this->assertSame(false, $this->sut->isRunning());
    }

    public function test_it_is_running_when_started(): void
    {
        $this->sut = new BatchStatus(BatchStatus::STARTED);
        $this->assertSame(true, $this->sut->isRunning());
    }

    public function test_it_is_running_when_starting(): void
    {
        $this->sut = new BatchStatus(BatchStatus::STARTING);
        $this->assertSame(true, $this->sut->isRunning());
    }

    public function test_it_is_stopping_when_stopping(): void
    {
        $this->sut = new BatchStatus(BatchStatus::STOPPING);
        $this->assertSame(true, $this->sut->isStopping());
    }

    public function test_it_is_unsuccessful_when_failed(): void
    {
        $this->sut = new BatchStatus(BatchStatus::FAILED);
        $this->assertSame(true, $this->sut->isUnsuccessful());
    }

    public function test_it_is_successful_when_completed(): void
    {
        $this->sut = new BatchStatus(BatchStatus::COMPLETED);
        $this->assertSame(false, $this->sut->isUnsuccessful());
    }

    public function test_it_is_successful_when_started(): void
    {
        $this->sut = new BatchStatus(BatchStatus::STARTED);
        $this->assertSame(false, $this->sut->isUnsuccessful());
    }

    public function test_it_is_successful_when_starting(): void
    {
        $this->sut = new BatchStatus(BatchStatus::STARTING);
        $this->assertSame(false, $this->sut->isUnsuccessful());
    }

    public function test_it_is_pausing_when_pausing(): void
    {
        $this->sut = new BatchStatus(BatchStatus::PAUSING);
        $this->assertSame(true, $this->sut->isPausing());
    }

    public function test_it_is_paused_when_paused(): void
    {
        $this->sut = new BatchStatus(BatchStatus::PAUSED);
        $this->assertSame(true, $this->sut->isPaused());
    }
}
