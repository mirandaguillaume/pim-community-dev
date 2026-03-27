<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Job\Unit\Application\SearchJobExecution\Model;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\StepExecutionTracking;
use Akeneo\Platform\Job\Domain\Model\Status;
use PHPUnit\Framework\TestCase;

class StepExecutionTrackingTest extends TestCase
{
    private StepExecutionTracking $sut;

    protected function setUp(): void
    {
        $this->sut = new StepExecutionTracking(
            1,
            10,
            0,
            false,
            200,
            100,
            true,
            Status::fromLabel('IN_PROGRESS'),
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(StepExecutionTracking::class, $this->sut);
    }

    public function test_it_normalizes_itself(): void
    {
        $this->assertSame([
            'id' => 1,
            'duration' => 10,
            'warning_count' => 0,
            'has_error' => false,
            'total_items' => 200,
            'processed_items' => 100,
            'is_trackable' => true,
            'status' => 'IN_PROGRESS',
        ], $this->sut->normalize());
    }

    public function test_it_returns_id(): void
    {
        $this->assertSame(1, $this->sut->getId());
    }

    public function test_it_returns_has_error(): void
    {
        $this->assertSame(false, $this->sut->hasError());
    }

    public function test_it_returns_warning_count(): void
    {
        $this->assertSame(0, $this->sut->getWarningCount());
    }
}
