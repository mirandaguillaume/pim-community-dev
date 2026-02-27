<?php

namespace spec\Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoggerSubscriberSpec extends ObjectBehavior
{
    public function let(LoggerInterface $logger, TranslatorInterface $translator)
    {
        $this->beConstructedWith($logger, $translator);
    }

    public function it_logs_job_execution_created($logger, JobExecutionEvent $event, JobExecution $jobExecution)
    {
        $event->getJobExecution()->willReturn($jobExecution);
        $jobExecution->__toString()->willReturn('job exec');
        $logger->debug('Job execution is created: job exec')->shouldBeCalled();
        $this->jobExecutionCreated($event);
    }

    public function it_logs_before_job_execution($logger, JobExecutionEvent $event, JobExecution $jobExecution)
    {
        $event->getJobExecution()->willReturn($jobExecution);
        $jobExecution->__toString()->willReturn('job exec');
        $logger->debug('Job execution starting: job exec')->shouldBeCalled();
        $this->beforeJobExecution($event);
    }
}
