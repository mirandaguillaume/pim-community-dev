<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Job\Unit\Application\SearchJobExecution\Model;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionTracking;
use Akeneo\Platform\Job\Application\SearchJobExecution\Model\StepExecutionTracking;
use Akeneo\Platform\Job\Domain\Model\Status;
use PHPUnit\Framework\TestCase;

class JobExecutionTrackingTest extends TestCase
{
    private JobExecutionTracking $sut;

    protected function setUp(): void
    {
        $firstStepExecutionTracking = new StepExecutionTracking(
            1,
            10,
            0,
            false,
            0,
            0,
            false,
            Status::fromLabel('COMPLETED'),
        );
        $secondStepExecutionTracking = new StepExecutionTracking(
            2,
            10,
            2,
            true,
            100,
            100,
            true,
            Status::fromLabel('COMPLETED'),
        );
        $this->sut = new JobExecutionTracking(2, 3, [$firstStepExecutionTracking, $secondStepExecutionTracking]);
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(JobExecutionTracking::class, $this->sut);
    }

    public function testItNormalizesItself(): void
    {
        $this->assertSame([
            'current_step' => 2,
            'total_step' => 3,
            'steps' => [
                [
                    'id' => 1,
                    'duration' => 10,
                    'warning_count' => 0,
                    'has_error' => false,
                    'total_items' => 0,
                    'processed_items' => 0,
                    'is_trackable' => false,
                    'status' => 'COMPLETED',
                ],
                [
                    'id' => 2,
                    'duration' => 10,
                    'warning_count' => 2,
                    'has_error' => true,
                    'total_items' => 100,
                    'processed_items' => 100,
                    'is_trackable' => true,
                    'status' => 'COMPLETED',
                ],
            ],
        ], $this->sut->normalize());
    }

    public function testItReturnsHasError(): void
    {
        $this->assertSame(true, $this->sut->hasError());
    }

    public function testItReturnsWarningCount(): void
    {
        $this->assertSame(2, $this->sut->getWarningCount());
    }

    public function testItCanBeConstructedOnlyWithAListOfStepExecutionTracking(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new JobExecutionTracking(1, 3, [1, 2, 3]);
    }
}
