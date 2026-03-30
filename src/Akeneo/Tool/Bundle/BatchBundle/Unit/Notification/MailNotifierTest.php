<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchBundle\Notification;

use Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifierInterface;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use spec\Akeneo\Tool\Bundle\BatchBundle\Notification\MailNotifier;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class MailNotifierTest extends TestCase
{
    private LoggerInterface|MockObject $logger;
    private TokenStorageInterface|MockObject $tokenStorage;
    private Environment|MockObject $twig;
    private MailNotifierInterface|MockObject $mailer;
    private MailNotifier $sut;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->mailer = $this->createMock(MailNotifierInterface::class);
        $this->sut = new MailNotifier($this->logger, $this->tokenStorage, $this->twig, $this->mailer);
        $this->twig->method('render')->with($this->isType('string'), $this->isType('array'))->willReturn('');
        $this->sut->setRecipients(['test@akeneo.com']);
    }

    public function test_it_notifies_a_successful_job(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);

        $batchStatus = new BatchStatus(BatchStatus::COMPLETED);
        $jobExecution->method('getStatus')->willReturn($batchStatus);
        $jobInstance->method('getLabel')->willReturn('An export');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $this->mailer->expects($this->once())->method('notify')->with(
            ['test@akeneo.com'],
            'Akeneo successfully completed your "An export" job',
            $this->anything(),
            $this->anything()
        );
        $this->sut->notify($jobExecution);
    }

    public function test_it_notifies_a_failed_job(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);

        $batchStatus = new BatchStatus(BatchStatus::UNKNOWN);
        $jobExecution->method('getStatus')->willReturn($batchStatus);
        $jobInstance->method('getLabel')->willReturn('Mass Edith');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $this->mailer->expects($this->once())->method('notify')->with(
            ['test@akeneo.com'],
            'Akeneo completed your "Mass Edith" job with errors',
            $this->anything(),
            $this->anything()
        );
        $this->sut->notify($jobExecution);
    }

    public function test_it_should_log_error_if_notification_failed(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);

        $batchStatus = new BatchStatus(BatchStatus::COMPLETED);
        $jobExecution->method('getStatus')->willReturn($batchStatus);
        $jobInstance->method('getLabel')->willReturn('An export');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $this->mailer->method('notify')->with(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->anything()
        )->willThrowException(\Throwable::class);
        $this->logger->expects($this->once())->method('error')->with($this->anything(), $this->anything());
        $this->sut->notify($jobExecution);
    }
}
