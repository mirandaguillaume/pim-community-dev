<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchQueueBundle\EventListener;

use Akeneo\Tool\Bundle\BatchQueueBundle\EventListener\AckMessageEventListener;
use Akeneo\Tool\Component\BatchQueue\Queue\ScheduledJobMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\UiJobExecutionMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

class AckMessageEventListenerTest extends TestCase
{
    private ContainerInterface|MockObject $receiverLocator;
    private AckMessageEventListener $sut;

    protected function setUp(): void
    {
        $this->receiverLocator = $this->createMock(ContainerInterface::class);
        $this->sut = new AckMessageEventListener($this->receiverLocator);
    }

    public function test_it_is_an_event_subscriber(): void
    {
        $this->assertInstanceOf(AckMessageEventListener::class, $this->sut);
    }

    public function test_it_does_nothing_when_message_is_not_a_job_message(): void
    {
        $envelope = new Envelope(new \stdClass());
        $event = new WorkerMessageReceivedEvent($envelope, 'receiver');
        $this->receiverLocator->expects($this->never())->method('get')->with($this->anything());
        $this->sut->ackMessage($event);
    }

    public function test_it_acks_the_message_for_a_job_message(): void
    {
        $receiver = $this->createMock(ReceiverInterface::class);

        $envelope = new Envelope(UiJobExecutionMessage::createJobExecutionMessage(1, []));
        $event = new WorkerMessageReceivedEvent($envelope, 'receiver_name');
        $this->receiverLocator->expects($this->once())->method('get')->with('receiver_name')->willReturn($receiver);
        $receiver->expects($this->once())->method('ack')->with($envelope);
        $this->sut->ackMessage($event);
    }

    public function test_it_acks_the_message_for_a_scheduled_message(): void
    {
        $receiver = $this->createMock(ReceiverInterface::class);

        $envelope = new Envelope(ScheduledJobMessage::createScheduledJobMessage("steven_job", []));
        $event = new WorkerMessageReceivedEvent($envelope, 'receiver_name');
        $this->receiverLocator->expects($this->once())->method('get')->with('receiver_name')->willReturn($receiver);
        $receiver->expects($this->once())->method('ack')->with($envelope);
        $this->sut->ackMessage($event);
    }
}
