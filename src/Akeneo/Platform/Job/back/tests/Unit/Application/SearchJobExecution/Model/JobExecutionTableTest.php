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

    protected function setUp(): void {}

    public function test_it_is_initializable(): void
    {
        $this->sut = new JobExecutionTable([], 5, 10);
        $this->assertTrue(is_a(JobExecutionTable::class, JobExecutionTable::class, true));
    }

    public function test_it_normalizes_itself(): void
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

    public function test_it_can_be_constructed_only_with_a_list_of_job_execution_row(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new JobExecutionTable([1], 5);
    }
}
