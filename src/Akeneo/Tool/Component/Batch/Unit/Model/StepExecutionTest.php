<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Model;

use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StepExecutionTest extends TestCase
{
    private JobExecution|MockObject $jobExecution;
    private StepExecution $sut;

    protected function setUp(): void
    {
        $this->jobExecution = $this->createMock(JobExecution::class);
        $this->sut = new StepExecution('myStepName', $this->jobExecution);
    }

    public function test_it_is_properly_instanciated(): void
    {
        $this->assertInstanceOf(BatchStatus::class, $this->sut->getStatus());
        $this->assertSame(BatchStatus::STARTING, $this->sut->getStatus()->getValue());
        $this->assertInstanceOf(ExitStatus::class, $this->sut->getExitStatus());
        $this->assertSame(ExitStatus::EXECUTING, $this->sut->getExitStatus()->getExitCode());
        $this->assertInstanceOf(ExecutionContext::class, $this->sut->getExecutionContext());
        $this->assertInstanceOf(ArrayCollection::class, $this->sut->getWarnings());
        $this->sut->getWarnings()->shouldBeEmpty();
        $this->assertInstanceOf('\DateTime', $this->sut->getStartTime());
        $this->assertCount(0, $this->sut->getFailureExceptions());
    }

    public function test_it_is_cloneable(): void
    {
        $clone = clone $this;
        $clone->shouldBeAnInstanceOf(StepExecution::class);
        $clone->getId()->shouldReturn(null);
    }

    public function test_it_upgrades_status(): void
    {
        $this->assertInstanceOf(BatchStatus::class, $this->sut->getStatus());
        $this->assertSame(BatchStatus::STARTING, $this->sut->getStatus()->getValue());
        $this->assertInstanceOf(StepExecution::class, $this->sut->upgradeStatus(BatchStatus::COMPLETED));
        $this->assertInstanceOf(BatchStatus::class, $this->sut->getStatus());
        $this->assertSame(BatchStatus::COMPLETED, $this->sut->getStatus()->getValue());
    }

    public function test_it_sets_exist_status(): void
    {
        $this->assertSame($this, $this->sut->setExitStatus(new ExitStatus(ExitStatus::NOOP, "foo")));
    }

    public function test_it_adds_a_failure_exception(): void
    {
        $exception = new \Exception('my msg');
        $this->assertSame($this, $this->sut->addFailureException($exception));
        $this->assertCount(1, $this->sut->getFailureExceptions());
    }

    public function test_it_adds_warning(): void
    {
        $invalidItem = $this->createMock(InvalidItemInterface::class);

        $this->assertSame(0, $this->sut->getWarningCount());
        $this->sut->addWarning(
            'my reason',
            [],
            $invalidItem
        );
        $this->assertCount(1, $this->sut->getWarnings());
        $this->assertSame(1, $this->sut->getWarningCount());
    }

    public function test_it_increments_summary_info(): void
    {
        $this->sut->incrementSummaryInfo('counter');
        $this->assertSame(1, $this->sut->getSummaryInfo('counter'));
        $this->sut->incrementSummaryInfo('counter', 3);
        $this->assertSame(4, $this->sut->getSummaryInfo('counter'));
    }

    public function test_it_gives_summary_info(): void
    {
        $this->assertSame('', $this->sut->getSummaryInfo('counter'));
        $this->assertSame(0, $this->sut->getSummaryInfo('counter', 0));
        $this->sut->incrementSummaryInfo('counter');
        $this->assertSame(1, $this->sut->getSummaryInfo('counter', 90));
    }

    public function test_it_is_displayable(): void
    {
        $this->assertSame('id=0, name=[myStepName], status=[2], exitCode=[EXECUTING], exitDescription=[]', $this->sut->__toString());
    }
}
