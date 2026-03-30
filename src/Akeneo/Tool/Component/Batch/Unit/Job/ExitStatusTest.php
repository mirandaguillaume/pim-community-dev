<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use PHPUnit\Framework\TestCase;

class ExitStatusTest extends TestCase
{
    private ExitStatus $sut;

    protected function setUp(): void
    {
    }

    public function test_it_has_no_description(): void
    {
        $this->sut = new ExitStatus("10");
        $this->assertSame('', $this->sut->getExitDescription());
    }

    public function test_it_sets_executing_exit_status(): void
    {
        $this->sut = new ExitStatus(ExitStatus::EXECUTING);
        $this->assertSame('EXECUTING', $this->sut->getExitCode());
    }

    public function test_it_compares_to_an_equal_status(): void
    {
        $this->sut = new ExitStatus(ExitStatus::EXECUTING);
        $otherStatus = new ExitStatus(ExitStatus::EXECUTING);
        $this->assertSame(0, $this->sut->compareTo($otherStatus));
    }

    public function test_it_compares_with_a_more_severe_status(): void
    {
        $this->sut = new ExitStatus(ExitStatus::EXECUTING);
        $otherStatus = new ExitStatus(ExitStatus::FAILED);
        $this->assertSame(-1, $this->sut->compareTo($otherStatus));
    }

    public function test_it_compares_with_a_less_severe_status(): void
    {
        $this->sut = new ExitStatus(ExitStatus::COMPLETED);
        $otherStatus = new ExitStatus(ExitStatus::EXECUTING);
        $this->assertSame(1, $this->sut->compareTo($otherStatus));
    }

    public function test_it_does_logical_and_between_statuses_by_setting_bigger_severity_from_other_status(): void
    {
        $this->sut = new ExitStatus(ExitStatus::COMPLETED);
        $otherStatus = new ExitStatus(ExitStatus::NOOP, 'my other desc');
        $this->sut->logicalAnd($otherStatus);
        $this->assertSame(ExitStatus::NOOP, $this->sut->getExitCode());
        $this->assertSame('my other desc', $this->sut->getExitDescription());
    }

    public function test_it_does_logical_and_between_statuses_by_setting_bigger_severity_from_main_status(): void
    {
        $this->sut = new ExitStatus(ExitStatus::NOOP);
        $otherStatus = new ExitStatus(ExitStatus::COMPLETED, 'my other desc');
        $this->sut->logicalAnd($otherStatus);
        $this->assertSame(ExitStatus::NOOP, $this->sut->getExitCode());
        $this->assertSame('my other desc', $this->sut->getExitDescription());
    }

    public function test_it_adds_exit_description_with_stacktrace(): void
    {
        $this->sut = new ExitStatus(ExitStatus::EXECUTING);
        $exception = new \Exception("Foo");
        $this->sut->addExitDescription($exception);
        $this->assertSame($exception->getTraceAsString(), $this->sut->getExitDescription());
    }

    public function test_it_does_not_duplicates_descriptions(): void
    {
        $this->sut = new ExitStatus(ExitStatus::EXECUTING);
        $this->sut->addExitDescription('Foo')->addExitDescription('Foo');
        $this->assertSame('Foo', $this->sut->getExitDescription());
    }

    public function test_it_adds_empty_description_to_existing_description(): void
    {
        $this->sut = new ExitStatus(ExitStatus::EXECUTING);
        $this->sut->addExitDescription('Foo')->addExitDescription(null);
        $this->assertSame('Foo', $this->sut->getExitDescription());
    }

    public function test_it_adds_an_exit_description_to_an_existing_description(): void
    {
        $this->sut = new ExitStatus(ExitStatus::EXECUTING);
        $this->sut->addExitDescription('Foo');
        $this->sut->addExitDescription('Bar');
        $this->assertSame('Foo;Bar', $this->sut->getExitDescription());
    }

    public function test_it_is_running_when_status_is_unknown(): void
    {
        $this->sut = new ExitStatus(ExitStatus::UNKNOWN);
        $this->assertSame(true, $this->sut->isRunning());
    }

    public function test_it_is_running_when_status_is_execution(): void
    {
        $this->sut = new ExitStatus(ExitStatus::EXECUTING);
        $this->assertSame(true, $this->sut->isRunning());
    }

    public function test_it_is_displayable(): void
    {
        $this->sut = new ExitStatus(ExitStatus::COMPLETED, 'My test description for completed status');
        $this->assertSame('[COMPLETED] My test description for completed status', $this->sut->__toString());
    }
}
