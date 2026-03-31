<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Component\Batch\Query\GetPausedJobExecutionIdsInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Akeneo\Tool\Component\BatchQueue\Queue\PausedJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishPausedJobsToQueue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PublishPausedJobsToQueueTest extends TestCase
{
    private JobExecutionQueueInterface|MockObject $jobExecutionQueue;
    private GetPausedJobExecutionIdsInterface|MockObject $getPausedJobExecutionIds;
    private LoggerInterface|MockObject $logger;
    private PublishPausedJobsToQueue $sut;

    protected function setUp(): void
    {
        $this->jobExecutionQueue = $this->createMock(JobExecutionQueueInterface::class);
        $this->getPausedJobExecutionIds = $this->createMock(GetPausedJobExecutionIdsInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->sut = new PublishPausedJobsToQueue(
            $this->jobExecutionQueue,
            $this->getPausedJobExecutionIds,
            $this->logger,
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(PublishPausedJobsToQueue::class, $this->sut);
    }

    public function test_it_publishes_a_paused_job_to_the_execution_queue(): void
    {
        $this->getPausedJobExecutionIds->method('all')->willReturn([1, 9]);
        $this->jobExecutionQueue->expects($this->exactly(2))->method('publish')->with($this->anything());
        $this->sut->publish();
    }

    public function test_it_does_not_fail_when_an_error_occurs_trying_to_publish_a_job(): void
    {
        $this->getPausedJobExecutionIds->method('all')->willReturn([1, 9]);
        $callCount = 0;
        $this->jobExecutionQueue->method('publish')->willReturnCallback(
            function (PausedJobExecutionMessage $jobMessage) use (&$callCount) {
                $callCount++;
                if ($jobMessage->getJobExecutionId() === 1) {
                    throw new \Exception();
                }
            }
        );
        $this->logger->expects($this->once())->method('error');
        $this->sut->publish();
    }
}
