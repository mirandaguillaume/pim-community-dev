<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchQueueBundle\Queue;

use Akeneo\Tool\Bundle\BatchQueueBundle\Queue\MessengerJobExecutionQueue;
use Akeneo\Tool\Component\BatchQueue\Queue\DataMaintenanceJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class MessengerJobExecutionQueueTest extends TestCase
{
    private MessageBusInterface|MockObject $bus;
    private MessengerJobExecutionQueue $sut;

    protected function setUp(): void
    {
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->sut = new MessengerJobExecutionQueue($this->bus);
    }

    public function test_it_is_job_execution_queue_interface(): void
    {
        $this->assertInstanceOf(JobExecutionQueueInterface::class, $this->sut);
        $this->assertInstanceOf(MessengerJobExecutionQueue::class, $this->sut);
    }

    public function test_it_publishes_job_execution_in_the_queue(): void
    {
        $jobExecutionMessage = DataMaintenanceJobExecutionMessage::createJobExecutionMessage(1, []);
        $envelope = new Envelope($jobExecutionMessage);
        $this->bus->expects($this->once())->method('dispatch')->with($jobExecutionMessage)->willReturn($envelope);
        $this->sut->publish($jobExecutionMessage);
    }
}
