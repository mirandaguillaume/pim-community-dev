<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Job\Unit\Application\SearchJobExecution\Model;

use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionRow;
use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionTable;
use Akeneo\Platform\Job\Application\SearchJobExecution\Model\JobExecutionTracking;
use Akeneo\Platform\Job\Domain\Model\Status;
use PHPUnit\Framework\TestCase;

class JobExecutionTableTest extends TestCase
{
    private JobExecutionTable $sut;

    protected function setUp(): void
    {
    }

    public function testItIsInitializable(): void
    {
        $this->sut = new JobExecutionTable([], 5);
        $this->assertTrue(is_a(JobExecutionTable::class, JobExecutionTable::class, true));
    }

    public function testItNormalizesItself(): void
    {
        $this->sut = new JobExecutionTable(
            [
                new JobExecutionRow(
                    1,
                    'jobName',
                    'export',
                    new \DateTimeImmutable('2021-11-02T11:20:27+02:00'),
                    'admin',
                    Status::fromLabel('COMPLETED'),
                    true,
                    new JobExecutionTracking(1, 2, []),
                ),
            ],
            1,
        );
        $this->assertSame([
            'rows' => [
                [
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
                        'total_step' => 2,
                        'steps' => [],
                    ],
                    'is_stoppable' => true,
                ],
            ],
            'matches_count' => 1,
        ], $this->sut->normalize());
    }

    public function testItCanBeConstructedOnlyWithAListOfJobExecutionRow(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new JobExecutionTable([1], 5);
    }
}
