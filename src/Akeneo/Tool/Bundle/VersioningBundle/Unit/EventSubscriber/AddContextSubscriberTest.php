<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber\AddContextSubscriber;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddContextSubscriberTest extends TestCase
{
    private VersionContext|MockObject $versionContext;
    private JobExecutionEvent|MockObject $event;
    private JobExecution|MockObject $jobExecution;
    private JobInstance|MockObject $jobInstance;
    private AddContextSubscriber $sut;

    protected function setUp(): void
    {
        $this->versionContext = $this->createMock(VersionContext::class);
        $this->event = $this->createMock(JobExecutionEvent::class);
        $this->jobExecution = $this->createMock(JobExecution::class);
        $this->jobInstance = $this->createMock(JobInstance::class);
        $this->sut = new AddContextSubscriber($this->versionContext);
        $this->event->method('getJobExecution')->willReturn($this->jobExecution);
        $this->jobExecution->method('getJobInstance')->willReturn($this->jobInstance);
    }

    public function test_it_injects_versioning_context_into_the_version_manager(): void
    {
        $this->jobInstance->method('getType')->willReturn(JobInstance::TYPE_IMPORT);
        $this->jobInstance->method('getCode')->willReturn('foo');
        $this->versionContext->expects($this->once())->method('addContextInfo')->with('import "foo"');
        $this->sut->addContext($this->event);
    }

    public function test_it_does_not_inject_context_if_the_job_is_not_an_import(): void
    {
        $this->jobInstance->method('getType')->willReturn(JobInstance::TYPE_EXPORT);
        $this->versionContext->expects($this->never())->method('addContextInfo')->with($this->anything());
        $this->sut->addContext($this->event);
    }
}
