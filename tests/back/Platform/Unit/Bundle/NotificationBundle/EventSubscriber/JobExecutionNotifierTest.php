<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Bundle\NotificationBundle\EventSubscriber;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\EventSubscriber\JobExecutionNotifier;
use Akeneo\Platform\Bundle\NotificationBundle\Factory\NotificationFactoryInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Factory\NotificationFactoryRegistry;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JobExecutionNotifierTest extends TestCase
{
    private NotificationFactoryRegistry|MockObject $factoryRegistry;
    private NotifierInterface|MockObject $notifier;
    private JobExecutionNotifier $sut;

    protected function setUp(): void
    {
        $this->factoryRegistry = $this->createMock(NotificationFactoryRegistry::class);
        $this->notifier = $this->createMock(NotifierInterface::class);
        $this->sut = new JobExecutionNotifier($this->factoryRegistry, $this->notifier);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(JobExecutionNotifier::class, $this->sut);
    }

    public function test_it_does_not_notify_if_job_execution_parameters_has_no_job_parameters(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $event = $this->createMock(JobExecutionEvent::class);

        $event->method('getJobExecution')->willReturn($jobExecution);
        $jobExecution->method('getJobParameters')->willReturn(null);
        $this->notifier->expects($this->never())->method('notify');
        $this->sut->afterJobExecution($event);
    }

    public function test_it_does_not_notify_if_job_execution_parameters_has_no_users_to_notify(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $event = $this->createMock(JobExecutionEvent::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $event->method('getJobExecution')->willReturn($jobExecution);
        $jobExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobParameters->method('has')->with('users_to_notify')->willReturn(false);
        $this->notifier->expects($this->never())->method('notify');
        $this->sut->afterJobExecution($event);
    }

    public function test_it_notifies_a_user_of_the_completion_of_job_execution(): void
    {
        [$event, $jobExecution] = $this->createStandardJobExecutionEvent('export', 'Product export');
        $notification = $this->createMock(NotificationInterface::class);
        $notificationFactory = $this->createMock(NotificationFactoryInterface::class);
        $exitStatus = $this->createMock(ExitStatus::class);

        $this->factoryRegistry->method('get')->with('export')->willReturn($notificationFactory);
        $notificationFactory->method('create')->with($jobExecution)->willReturn($notification);
        $notification->method('setMessage')->willReturnSelf();
        $notification->method('setMessageParams')->willReturnSelf();
        $notification->method('setRoute')->willReturnSelf();
        $notification->method('setRouteParams')->willReturnSelf();
        $notification->method('setContext')->willReturnSelf();
        $notification->method('setType')->willReturnSelf();
        $jobExecution->method('getExitStatus')->willReturn($exitStatus);
        $exitStatus->method('getExitCode')->willReturn(ExitStatus::COMPLETED);
        $this->notifier->expects($this->once())->method('notify')->with($notification, ['julia']);
        $this->sut->afterJobExecution($event);
    }

    public function test_it_notifies_a_user_of_the_completion_of_a_mass_edit_job_execution(): void
    {
        [$event, $jobExecution] = $this->createStandardJobExecutionEvent('mass_edit', 'Product mass edit');
        $notification = $this->createMock(NotificationInterface::class);
        $notificationFactory = $this->createMock(NotificationFactoryInterface::class);
        $exitStatus = $this->createMock(ExitStatus::class);

        $this->factoryRegistry->method('get')->with('mass_edit')->willReturn($notificationFactory);
        $notificationFactory->method('create')->with($jobExecution)->willReturn($notification);
        $notification->method('setMessage')->willReturnSelf();
        $notification->method('setMessageParams')->willReturnSelf();
        $notification->method('setRoute')->willReturnSelf();
        $notification->method('setRouteParams')->willReturnSelf();
        $notification->method('setContext')->willReturnSelf();
        $notification->method('setType')->willReturnSelf();
        $jobExecution->method('getExitStatus')->willReturn($exitStatus);
        $exitStatus->method('getExitCode')->willReturn(ExitStatus::COMPLETED);
        $this->notifier->expects($this->once())->method('notify')->with($notification, ['julia']);
        $this->sut->afterJobExecution($event);
    }

    public function test_it_notifies_a_user_of_the_completion_of_job_execution_which_has_encountered_a_warning(): void
    {
        [$event, $jobExecution] = $this->createStandardJobExecutionEvent('export', 'Product export');
        $notification = $this->createMock(NotificationInterface::class);
        $notificationFactory = $this->createMock(NotificationFactoryInterface::class);
        $exitStatus = $this->createMock(ExitStatus::class);

        $this->factoryRegistry->method('get')->with('export')->willReturn($notificationFactory);
        $notificationFactory->method('create')->with($jobExecution)->willReturn($notification);
        $notification->method('setType')->willReturnSelf();
        $notification->method('setMessage')->willReturnSelf();
        $notification->method('setMessageParams')->willReturnSelf();
        $notification->method('setRoute')->willReturnSelf();
        $notification->method('setRouteParams')->willReturnSelf();
        $notification->method('setContext')->willReturnSelf();
        $this->notifier->expects($this->once())->method('notify')->with($notification, ['julia']);
        $jobExecution->method('getExitStatus')->willReturn($exitStatus);
        $exitStatus->method('getExitCode')->willReturn(ExitStatus::COMPLETED);
        $this->sut->afterJobExecution($event);
    }

    public function test_it_notifies_a_user_of_the_completion_of_job_execution_which_has_encountered_an_error(): void
    {
        [$event, $jobExecution] = $this->createStandardJobExecutionEvent('export', 'Product export');
        $notification = $this->createMock(NotificationInterface::class);
        $notificationFactory = $this->createMock(NotificationFactoryInterface::class);
        $exitStatus = $this->createMock(ExitStatus::class);

        $this->factoryRegistry->method('get')->with('export')->willReturn($notificationFactory);
        $notificationFactory->method('create')->with($jobExecution)->willReturn($notification);
        $notification->method('setType')->willReturnSelf();
        $notification->method('setMessage')->willReturnSelf();
        $notification->method('setMessageParams')->willReturnSelf();
        $notification->method('setRoute')->willReturnSelf();
        $notification->method('setRouteParams')->willReturnSelf();
        $notification->method('setContext')->willReturnSelf();
        $jobExecution->method('getExitStatus')->willReturn($exitStatus);
        $exitStatus->method('getExitCode')->willReturn(ExitStatus::COMPLETED);
        $this->notifier->expects($this->once())->method('notify')->with($notification, ['julia']);
        $this->sut->afterJobExecution($event);
    }

    public function test_it_does_not_notify_a_user_of_the_completion_of_job_execution_which_has_been_stopped(): void
    {
        [$event, $jobExecution] = $this->createStandardJobExecutionEvent('export', 'Product export');
        $notification = $this->createMock(NotificationInterface::class);
        $notificationFactory = $this->createMock(NotificationFactoryInterface::class);
        $exitStatus = $this->createMock(ExitStatus::class);

        $this->factoryRegistry->method('get')->with('export')->willReturn($notificationFactory);
        $notificationFactory->method('create')->with($jobExecution)->willReturn($notification);
        $notification->method('setType')->willReturnSelf();
        $notification->method('setMessage')->willReturnSelf();
        $notification->method('setMessageParams')->willReturnSelf();
        $notification->method('setRoute')->willReturnSelf();
        $notification->method('setRouteParams')->willReturnSelf();
        $notification->method('setContext')->willReturnSelf();
        $jobExecution->method('getExitStatus')->willReturn($exitStatus);
        $exitStatus->method('getExitCode')->willReturn(ExitStatus::STOPPED);
        $this->notifier->expects($this->never())->method('notify');
        $this->sut->afterJobExecution($event);
    }

    public function test_it_throws_an_exception_when_factory_is_not_found(): void
    {
        [$event, $jobExecution] = $this->createStandardJobExecutionEvent('export', 'Product export');
        $exitStatus = $this->createMock(ExitStatus::class);

        $this->factoryRegistry->method('get')->with('export')->willReturn(null);
        $jobExecution->method('getExitStatus')->willReturn($exitStatus);
        $exitStatus->method('getExitCode')->willReturn(ExitStatus::COMPLETED);
        $this->expectException(\LogicException::class);
        $this->sut->afterJobExecution($event);
    }

    /**
     * @return array{JobExecutionEvent|MockObject, JobExecution|MockObject}
     */
    private function createStandardJobExecutionEvent(string $type, string $label): array
    {
        $event = $this->createMock(JobExecutionEvent::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $warnings = $this->createMock(ArrayCollection::class);
        $status = $this->createMock(BatchStatus::class);

        $event->method('getJobExecution')->willReturn($jobExecution);
        $jobExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobExecution->method('getStepExecutions')->willReturn([$stepExecution]);
        $jobExecution->method('getStatus')->willReturn($status);
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $jobExecution->method('getId')->willReturn(5);
        $jobParameters->method('has')->with('users_to_notify')->willReturn(true);
        $jobParameters->method('get')->with('users_to_notify')->willReturn(['julia']);
        $stepExecution->method('getWarnings')->willReturn($warnings);
        $jobInstance->method('getType')->willReturn($type);
        $jobInstance->method('getLabel')->willReturn($label);

        return [$event, $jobExecution];
    }
}
