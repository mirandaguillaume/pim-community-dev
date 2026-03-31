<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Bundle\BatchBundle\EventListener\NotificationSubscriber;
use Akeneo\Tool\Bundle\BatchBundle\Notification\Notifier;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotificationSubscriberTest extends TestCase
{
    private NotificationSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new NotificationSubscriber();
    }

    public function test_it_notifies_notifier(): void
    {
        $notifier1 = $this->createMock(Notifier::class);
        $notifier2 = $this->createMock(Notifier::class);
        $event = $this->createMock(JobExecutionEvent::class);
        $execution = $this->createMock(JobExecution::class);

        $this->sut->registerNotifier($notifier1);
        $this->sut->registerNotifier($notifier2);
        $event->method('getJobExecution')->willReturn($execution);
        $notifier1->expects($this->once())->method('notify')->with($execution);
        $notifier2->expects($this->once())->method('notify')->with($execution);
        $this->sut->afterJobExecution($event);
    }
}
