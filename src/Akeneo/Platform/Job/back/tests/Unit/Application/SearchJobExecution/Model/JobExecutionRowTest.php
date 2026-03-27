<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Job\Unit\Application\SearchJobExecution\Model;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionRow;
use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionTracking;
use Akeneo\Platform\Job\Domain\Model\Status;
use PHPUnit\Framework\TestCase;

class JobExecutionRowTest extends TestCase
{
    private JobExecutionRow $sut;

    protected function setUp(): void
    {
        $this->sut = new JobExecutionRow(
            1,
            'jobName',
            'export',
            new \DateTimeImmutable('2021-11-02T11:20:27+02:00'),
            'admin',
            Status::fromLabel('COMPLETED'),
            true,
            new JobExecutionTracking(1, 3, [])
        );
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(JobExecutionRow::class, $this->sut);
    }

    public function testItNormalizesItself(): void
    {
        $this->assertSame([
            'job_execution_id' => 1,
            'job_name' => 'jobName',
            'type' => 'export',
            'started_at' => '2021-11-02T11:20:27+02:00',
            'username' => 'admin',
            'status' => 'COMPLETED',
            'warning_count' => 0,
            'has_error' => false,
            'tracking' => [
                'current_step' => 1,
                'total_step' => 3,
                'steps' => [],
            ],
            'is_stoppable' => true,
        ], $this->sut->normalize());
    }

    public function testItNormalizesItselfWithNullValue(): void
    {
        $this->sut = new JobExecutionRow(
            1,
            'jobName',
            'export',
            null,
            null,
            Status::fromLabel('COMPLETED'),
            false,
            new JobExecutionTracking(1, 1, [])
        );
        $this->assertSame([
            'job_execution_id' => 1,
            'job_name' => 'jobName',
            'type' => 'export',
            'started_at' => null,
            'username' => null,
            'status' => 'COMPLETED',
            'warning_count' => 0,
            'has_error' => false,
            'tracking' => [
                'current_step' => 1,
                'total_step' => 1,
                'steps' => [],
            ],
            'is_stoppable' => false,
        ], $this->sut->normalize());
    }
}
