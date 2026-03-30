<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use spec\Akeneo\Tool\Bundle\BatchBundle\EventListener\LoggerSubscriber;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoggerSubscriberTest extends TestCase
{
    private LoggerInterface|MockObject $logger;
    private TranslatorInterface|MockObject $translator;
    private LoggerSubscriber $sut;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->sut = new LoggerSubscriber($this->logger, $this->translator);
    }

    public function test_it_logs_job_execution_created(): void
    {
        $event = $this->createMock(JobExecutionEvent::class);
        $jobExecution = $this->createMock(JobExecution::class);

        $event->method('getJobExecution')->willReturn($jobExecution);
        $jobExecution->method('__toString')->willReturn('job exec');
        $this->logger->expects($this->once())->method('debug')->with('Job execution is created: job exec');
        $this->sut->jobExecutionCreated($event);
    }

    public function test_it_logs_before_job_execution(): void
    {
        $event = $this->createMock(JobExecutionEvent::class);
        $jobExecution = $this->createMock(JobExecution::class);

        $event->method('getJobExecution')->willReturn($jobExecution);
        $jobExecution->method('__toString')->willReturn('job exec');
        $this->logger->expects($this->once())->method('debug')->with('Job execution starting: job exec');
        $this->sut->beforeJobExecution($event);
    }
}
