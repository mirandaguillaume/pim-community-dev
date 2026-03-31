<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Bundle\BatchBundle\EventListener\SetJobExecutionLogFileSubscriber;
use Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetJobExecutionLogFileSubscriberTest extends TestCase
{
    private BatchLogHandler|MockObject $handler;
    private SetJobExecutionLogFileSubscriber $sut;

    protected function setUp(): void
    {
        $this->handler = $this->createMock(BatchLogHandler::class);
        $this->sut = new SetJobExecutionLogFileSubscriber($this->handler);
    }

    public function test_it_sets_job_execution_log_file(): void
    {
        $event = $this->createMock(JobExecutionEvent::class);
        $execution = $this->createMock(JobExecution::class);

        $this->handler->method('getFilename')->willReturn('myfilename');
        $event->method('getJobExecution')->willReturn($execution);
        $execution->expects($this->once())->method('setLogFile')->with('myfilename');
        $this->sut->setJobExecutionLogFile($event);
    }
}
