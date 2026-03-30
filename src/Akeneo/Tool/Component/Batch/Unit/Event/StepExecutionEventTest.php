<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Event;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;

class StepExecutionEventTest extends TestCase
{
    private StepExecution|MockObject $stepExecution;
    private StepExecutionEvent $sut;

    protected function setUp(): void
    {
        $this->stepExecution = $this->createMock(StepExecution::class);
        $this->sut = new StepExecutionEvent($this->stepExecution);
    }

    public function test_it_provides_the_step_execution(): void
    {
        $this->assertSame($this->stepExecution, $this->sut->getStepExecution());
    }
}
