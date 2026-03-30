<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Batch\Step;

use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Item\StatefulInterface;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Job\JobStopperInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Model\Warning;
use Akeneo\Tool\Component\Batch\spec\Step\StatefulReaderInterface;
use Akeneo\Tool\Component\Batch\spec\Step\StatefulWriterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Batch\Step\ItemStep;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ItemStepTest extends TestCase
{
    private EventDispatcherInterface|MockObject $dispatcher;
    private DoctrineJobRepository|MockObject $repository;
    private PausableReader|MockObject $reader;
    private ItemProcessorInterface|MockObject $processor;
    private PausableWriter|MockObject $writer;
    private JobStopperInterface|MockObject $jobStopper;
    private ItemStep $sut;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->repository = $this->createMock(DoctrineJobRepository::class);
        $this->reader = $this->createMock(PausableReader::class);
        $this->processor = $this->createMock(ItemProcessorInterface::class);
        $this->writer = $this->createMock(PausableWriter::class);
        $this->jobStopper = $this->createMock(JobStopperInterface::class);
        $this->sut = new ItemStep(
            'myname',
            $this->dispatcher,
            $this->repository,
            $this->reader,
            $this->processor,
            $this->writer,
            3,
            $this->jobStopper
        );
    }

    public function test_it_executes_with_success(): void
    {
        $execution = $this->createMock(StepExecution::class);

        $this->reader->expects($this->once())->method('setState')->with([]);
        $this->writer->expects($this->once())->method('setState')->with([]);
        $execution->method('getStatus')->willReturn(new BatchStatus(BatchStatus::STARTING));
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::BEFORE_STEP_EXECUTION);
        $execution->expects($this->once())->method('setStatus')->with($this->anything());
        $execution->method('getCurrentState')->willReturn([]);
        // first batch
        $this->reader->read()->willReturn('r1', 'r2', 'r3', 'r4', null);
        $this->processor->expects($this->once())->method('process')->with('r1')->willReturn('p1');
        $this->processor->expects($this->once())->method('process')->with('r2')->willReturn('p2');
        $this->processor->expects($this->once())->method('process')->with('r3')->willReturn('p3');
        $this->writer->expects($this->once())->method('write')->with(['p1', 'p2', 'p3']);
        $execution->expects($this->once())->method('incrementProcessedItems')->with(3);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::ITEM_STEP_AFTER_BATCH);
        $this->jobStopper->method('isStopping')->with($execution)->willReturn(false);
        $this->jobStopper->method('isPausing')->with($execution)->willReturn(false);
        // second batch
        $this->processor->process('r4')->shouldBeCalled()->willReturn('p4');
        $this->processor->expects($this->never())->method('process')->with(null);
        $this->writer->expects($this->once())->method('write')->with(['p4']);
        $execution->expects($this->once())->method('incrementProcessedItems')->with(1);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::ITEM_STEP_AFTER_BATCH);
        $this->jobStopper->method('isStopping')->with($execution)->willReturn(false);
        $this->jobStopper->method('isPausing')->with($execution)->willReturn(false);
        $this->writer->expects($this->once())->method('flush');
        $exitStatus = new ExitStatus(ExitStatus::COMPLETED, "");
        $execution->method('getExitStatus')->willReturn($exitStatus);
        $this->repository->expects($this->exactly(5))->method('updateStepExecution')->with($execution);
        $execution->method('isTerminateOnly')->willReturn(false);
        $execution->expects($this->once())->method('upgradeStatus')->with($this->anything());
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::STEP_EXECUTION_SUCCEEDED);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::STEP_EXECUTION_COMPLETED);
        $execution->expects($this->once())->method('setEndTime')->with($this->anything());
        $execution->expects($this->once())->method('setExitStatus')->with($this->anything());
        $this->sut->execute($execution);
    }

    public function test_it_executes_with_an_invalid_item_during_processing(): void
    {
        $execution = $this->createMock(StepExecution::class);

        $this->reader->expects($this->once())->method('setState')->with([]);
        $this->writer->expects($this->once())->method('setState')->with([]);
        $execution->method('getStatus')->willReturn(new BatchStatus(BatchStatus::STARTING));
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::BEFORE_STEP_EXECUTION);
        $execution->expects($this->once())->method('setStatus')->with($this->anything());
        $execution->method('getCurrentState')->willReturn([]);
        // first batch
        $this->reader->read()->willReturn('r1', 'r2', 'r3', 'r4', null);
        $this->processor->expects($this->once())->method('process')->with('r1')->willReturn('p1');
        $this->processor->expects($this->once())->method('process')->with('r2')->willReturn('p2');
        $this->processor->expects($this->once())->method('process')->with('r3')->willReturn('p3');
        $this->writer->expects($this->once())->method('write')->with(['p1', 'p2', 'p3']);
        $execution->expects($this->once())->method('incrementProcessedItems')->with(3);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::ITEM_STEP_AFTER_BATCH);
        $this->jobStopper->method('isStopping')->with($execution)->willReturn(false);
        $this->jobStopper->method('isPausing')->with($execution)->willReturn(false);
        // second batch
        $this->processor->process('r4')->shouldBeCalled()->willThrow(
            new InvalidItemException('my msg', new FileInvalidItem(['r4'], 7))
        );
        $execution->expects($this->once())->method('incrementProcessedItems')->with(1);
        $warning = new Warning($execution, 'my msg', [], ['r4']);
        $this->repository->expects($this->once())->method('addWarning')->with($warning);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), $this->anything());
        $this->processor->expects($this->never())->method('process')->with(null);
        $this->writer->expects($this->never())->method('write')->with(['p4']);
        $this->writer->expects($this->once())->method('flush');
        $exitStatus = new ExitStatus(ExitStatus::COMPLETED, "");
        $execution->method('getExitStatus')->willReturn($exitStatus);
        $this->repository->expects($this->exactly(5))->method('updateStepExecution')->with($execution);
        $execution->method('isTerminateOnly')->willReturn(false);
        $execution->expects($this->once())->method('upgradeStatus')->with($this->anything());
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::STEP_EXECUTION_SUCCEEDED);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::STEP_EXECUTION_COMPLETED);
        $execution->expects($this->once())->method('setEndTime')->with($this->anything());
        $execution->expects($this->once())->method('setExitStatus')->with($this->anything());
        $this->sut->execute($execution);
    }

    public function test_it_not_not_write_item_not_processed(): void
    {
        $execution = $this->createMock(StepExecution::class);

        $this->reader->expects($this->once())->method('setState')->with([]);
        $this->writer->expects($this->once())->method('setState')->with([]);
        $execution->method('getStatus')->willReturn(new BatchStatus(BatchStatus::STARTING));
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::BEFORE_STEP_EXECUTION);
        $execution->expects($this->once())->method('setStatus')->with($this->anything());
        $execution->method('getCurrentState')->willReturn([]);
        $this->jobStopper->method('isStopping')->with($execution)->willReturn(false);
        $this->jobStopper->method('isPausing')->with($execution)->willReturn(false);
        // first batch
        $this->reader->read()->willReturn('r1', 'r2', 'r3', 'r4', null);
        $this->processor->expects($this->once())->method('process')->with('r1')->willReturn('p1');
        $this->processor->expects($this->once())->method('process')->with('r2')->willReturn(null);
        $this->processor->expects($this->once())->method('process')->with('r3')->willReturn('p3');
        $this->writer->expects($this->once())->method('write')->with(['p1', 'p3']);
        $execution->expects($this->once())->method('incrementProcessedItems')->with(3);
        // second batch
        $this->processor->process('r4')->shouldBeCalled()->willReturn('p4');
        $execution->expects($this->once())->method('incrementProcessedItems')->with(1);
        $this->dispatcher->expects($this->exactly(2))->method('dispatch')->with($this->anything(), EventInterface::ITEM_STEP_AFTER_BATCH);
        $this->processor->expects($this->never())->method('process')->with(null);
        $this->writer->expects($this->once())->method('write')->with(['p4']);
        $this->writer->expects($this->once())->method('flush');
        $exitStatus = new ExitStatus(ExitStatus::COMPLETED, "");
        $execution->method('getExitStatus')->willReturn($exitStatus);
        $this->repository->expects($this->exactly(5))->method('updateStepExecution')->with($execution);
        $execution->method('isTerminateOnly')->willReturn(false);
        $execution->expects($this->once())->method('upgradeStatus')->with($this->anything());
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::STEP_EXECUTION_SUCCEEDED);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::STEP_EXECUTION_COMPLETED);
        $execution->expects($this->once())->method('setEndTime')->with($this->anything());
        $execution->expects($this->once())->method('setExitStatus')->with($this->anything());
        $this->sut->execute($execution);
    }

    public function test_it_stop_if_asked(): void
    {
        $execution = $this->createMock(StepExecution::class);

        $this->reader->expects($this->once())->method('setState')->with([]);
        $this->writer->expects($this->once())->method('setState')->with([]);
        $execution->method('getStatus')->willReturn(new BatchStatus(BatchStatus::STARTING));
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::BEFORE_STEP_EXECUTION);
        $execution->expects($this->once())->method('setStatus')->with($this->anything());
        $execution->method('getCurrentState')->willReturn([]);
        $this->jobStopper->method('isStopping')->with($execution)->willReturn(false);
        $this->jobStopper->method('isPausing')->with($execution)->willReturn(false);
        // first batch
        $this->reader->read()->willReturn('r1', 'r2', 'r3', 'r4', null);
        $this->processor->expects($this->once())->method('process')->with('r1')->willReturn('p1');
        $this->processor->expects($this->once())->method('process')->with('r2')->willReturn(null);
        $this->processor->expects($this->once())->method('process')->with('r3')->willReturn('p3');
        $this->writer->expects($this->once())->method('write')->with(['p1', 'p3']);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::ITEM_STEP_AFTER_BATCH);
        $execution->expects($this->once())->method('incrementProcessedItems')->with(3);
        // second batch
        $this->jobStopper->isStopping($execution)->willReturn(true);
        $this->jobStopper->expects($this->once())->method('stop')->with($execution);
        $this->writer->expects($this->once())->method('flush');
        $exitStatus = new ExitStatus(ExitStatus::STOPPED, "");
        $execution->method('getExitStatus')->willReturn($exitStatus);
        $this->repository->expects($this->exactly(4))->method('updateStepExecution')->with($execution);
        $execution->method('isTerminateOnly')->willReturn(false);
        $execution->expects($this->once())->method('upgradeStatus')->with($this->anything());
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::STEP_EXECUTION_SUCCEEDED);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::STEP_EXECUTION_COMPLETED);
        $execution->expects($this->once())->method('setEndTime')->with($this->anything());
        $execution->expects($this->once())->method('setExitStatus')->with($this->anything());
        $this->sut->execute($execution);
    }

    public function test_it_pause_if_asked(): void
    {
        $execution = $this->createMock(StepExecution::class);

        $this->reader->expects($this->once())->method('setState')->with([]);
        $this->writer->expects($this->once())->method('setState')->with([]);
        $execution->method('getStatus')->willReturn(new BatchStatus(BatchStatus::STARTING));
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::BEFORE_STEP_EXECUTION);
        $execution->expects($this->once())->method('setStatus')->with($this->anything());
        $execution->method('getCurrentState')->willReturn([]);
        $this->reader->expects($this->once())->method('setState')->with([]);
        $this->writer->expects($this->once())->method('setState')->with([]);
        $this->jobStopper->method('isStopping')->with($execution)->willReturn(false);
        $this->jobStopper->method('isPausing')->with($execution)->willReturn(false);
        // first batch
        $this->reader->read()->willReturn('r1', 'r2', 'r3', 'r4', null);
        $this->processor->expects($this->once())->method('process')->with('r1')->willReturn('p1');
        $this->processor->expects($this->once())->method('process')->with('r2')->willReturn(null);
        $this->processor->expects($this->once())->method('process')->with('r3')->willReturn('p3');
        $this->writer->expects($this->once())->method('write')->with(['p1', 'p3']);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::ITEM_STEP_AFTER_BATCH);
        $execution->expects($this->once())->method('incrementProcessedItems')->with(3);
        // second batch
        $this->jobStopper->isPausing($execution)->willReturn(true);
        $this->reader->method('getState')->willReturn(['position' => 1]);
        $this->writer->method('getState')->willReturn(['file_path' => '/tmp/file.xslx']);
        $this->jobStopper->expects($this->once())->method('pause')->with($execution, ['reader' => ['position' => 1], 'writer' => ['file_path' => '/tmp/file.xslx']]);
        $this->writer->expects($this->never())->method('flush');
        $exitStatus = new ExitStatus(ExitStatus::COMPLETED, "");
        $execution->method('getExitStatus')->willReturn($exitStatus);
        $this->repository->expects($this->exactly(4))->method('updateStepExecution')->with($execution);
        $execution->method('isTerminateOnly')->willReturn(false);
        $execution->expects($this->once())->method('upgradeStatus')->with($this->anything());
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::STEP_EXECUTION_SUCCEEDED);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::STEP_EXECUTION_COMPLETED);
        $execution->expects($this->once())->method('setEndTime')->with($this->anything());
        $execution->expects($this->once())->method('setExitStatus')->with($this->anything());
        $this->sut->execute($execution);
    }

    public function test_it_resumes_paused_job_with_success(): void
    {
        $execution = $this->createMock(StepExecution::class);

        $this->reader->expects($this->once())->method('setState')->with([]);
        $this->writer->expects($this->once())->method('setState')->with([]);
        $execution->method('getStatus')->willReturn(new BatchStatus(BatchStatus::PAUSED));
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::BEFORE_STEP_EXECUTION_RESUME);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::BEFORE_STEP_EXECUTION);
        $execution->expects($this->once())->method('setStatus')->with($this->callback(fn (BatchStatus $newStatus) => $execution->getStatus()->willReturn($newStatus)))->willReturn($newStatus);
        $execution->method('getCurrentState')->willReturn([]);
        // first batch
        $this->reader->read()->willReturn('r1', 'r2', 'r3', 'r4', null);
        $this->processor->expects($this->once())->method('process')->with('r1')->willReturn('p1');
        $this->processor->expects($this->once())->method('process')->with('r2')->willReturn('p2');
        $this->processor->expects($this->once())->method('process')->with('r3')->willReturn('p3');
        $this->writer->expects($this->once())->method('write')->with(['p1', 'p2', 'p3']);
        $execution->expects($this->once())->method('incrementProcessedItems')->with(3);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::ITEM_STEP_AFTER_BATCH);
        $this->jobStopper->method('isStopping')->with($execution)->willReturn(false);
        $this->jobStopper->method('isPausing')->with($execution)->willReturn(false);
        // second batch
        $this->processor->process('r4')->shouldBeCalled()->willReturn('p4');
        $this->processor->expects($this->never())->method('process')->with(null);
        $this->writer->expects($this->once())->method('write')->with(['p4']);
        $execution->expects($this->once())->method('incrementProcessedItems')->with(1);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::ITEM_STEP_AFTER_BATCH);
        $this->jobStopper->method('isStopping')->with($execution)->willReturn(false);
        $this->jobStopper->method('isPausing')->with($execution)->willReturn(false);
        $this->writer->expects($this->once())->method('flush');
        $exitStatus = new ExitStatus(ExitStatus::COMPLETED, "");
        $execution->method('getExitStatus')->willReturn($exitStatus);
        $this->repository->expects($this->exactly(5))->method('updateStepExecution')->with($execution);
        $execution->method('isTerminateOnly')->willReturn(false);
        $execution->expects($this->once())->method('upgradeStatus')->with($this->anything());
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::STEP_EXECUTION_SUCCEEDED);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::STEP_EXECUTION_COMPLETED);
        $execution->expects($this->once())->method('setEndTime')->with($this->anything());
        $execution->expects($this->once())->method('setExitStatus')->with($this->anything());
        $this->sut->execute($execution);
    }

    public function test_it_executes_a_job_and_set_as_paused(): void
    {
        $execution = $this->createMock(StepExecution::class);

        $this->reader->expects($this->once())->method('setState')->with([]);
        $this->writer->expects($this->once())->method('setState')->with([]);
        $pausedStatus = new BatchStatus(BatchStatus::PAUSED);
        $execution->method('getStatus')->willReturn($pausedStatus);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::BEFORE_STEP_EXECUTION_RESUME);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::BEFORE_STEP_EXECUTION);
        $execution->expects($this->once())->method('setStatus')->with($this->callback(fn (BatchStatus $newStatus) => $execution->getStatus()->willReturn($newStatus)))->willReturn($newStatus);
        $execution->method('getCurrentState')->willReturn([]);
        // first batch
        $this->reader->read()->willReturn('r1', 'r2', 'r3', 'r4', null);
        $this->processor->expects($this->once())->method('process')->with('r1')->willReturn('p1');
        $this->processor->expects($this->once())->method('process')->with('r2')->willReturn('p2');
        $this->processor->expects($this->once())->method('process')->with('r3')->willReturn('p3');
        $this->writer->expects($this->once())->method('write')->with(['p1', 'p2', 'p3']);
        $execution->expects($this->once())->method('incrementProcessedItems')->with(3);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::ITEM_STEP_AFTER_BATCH);
        $this->jobStopper->method('isStopping')->with($execution)->willReturn(false);
        $this->jobStopper->method('isPausing')->with($execution)->willReturn(true);
        $this->jobStopper->method('pause')->with($execution, ['reader' => [], 'writer' => []]);
        $this->reader->method('getState')->willReturn([]);
        $this->writer->method('getState')->willReturn([]);
        $this->writer->expects($this->never())->method('flush');
        $exitStatus = new ExitStatus(ExitStatus::COMPLETED, "");
        $execution->method('getExitStatus')->willReturn($exitStatus);
        $this->repository->expects($this->exactly(4))->method('updateStepExecution')->with($execution);
        $execution->method('isTerminateOnly')->willReturn(false);
        $execution->expects($this->once())->method('upgradeStatus')->with($this->callback(fn () => $execution->getStatus()->willReturn($pausedStatus)))->willReturn($pausedStatus);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::STEP_EXECUTION_SUCCEEDED);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::STEP_EXECUTION_COMPLETED);
        $execution->expects($this->never())->method('setEndTime')->with($this->anything());
        $execution->expects($this->once())->method('setExitStatus')->with($this->anything());
        $this->sut->execute($execution);
    }

    public function test_it_flushes_step_elements_when_job_is_pausing_and_every_items_are_processed(): void
    {
        $execution = $this->createMock(StepExecution::class);

        $execution->method('getStatus')->willReturn(new BatchStatus(BatchStatus::STARTING));
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::BEFORE_STEP_EXECUTION);
        $execution->expects($this->once())->method('setStatus')->with($this->anything());
        $execution->method('getCurrentState')->willReturn([]);
        $this->reader->expects($this->once())->method('setState')->with([]);
        $this->writer->expects($this->once())->method('setState')->with([]);
        $this->jobStopper->method('isStopping')->with($execution)->willReturn(false);
        $this->jobStopper->method('isPausing')->with($execution)->willReturn(false);
        $this->reader->method('read')->willReturn('r1', 'r2', 'r3', null);
        $this->processor->expects($this->once())->method('process')->with('r1')->willReturn('p1');
        $this->processor->expects($this->once())->method('process')->with('r2')->willReturn('p2');
        $this->processor->expects($this->once())->method('process')->with('r3')->willReturn('p3');
        $this->writer->expects($this->once())->method('write')->with(['p1', 'p2', 'p3']);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::ITEM_STEP_AFTER_BATCH);
        $execution->expects($this->once())->method('incrementProcessedItems')->with(3);
        $this->writer->expects($this->once())->method('flush');
        $exitStatus = new ExitStatus(ExitStatus::COMPLETED, "");
        $execution->method('getExitStatus')->willReturn($exitStatus);
        $this->repository->expects($this->exactly(4))->method('updateStepExecution')->with($execution);
        $execution->method('isTerminateOnly')->willReturn(false);
        $execution->expects($this->once())->method('upgradeStatus')->with($this->anything());
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::STEP_EXECUTION_SUCCEEDED);
        $this->dispatcher->expects($this->once())->method('dispatch')->with($this->anything(), EventInterface::STEP_EXECUTION_COMPLETED);
        $execution->expects($this->once())->method('setEndTime')->with($this->anything());
        $execution->expects($this->once())->method('setExitStatus')->with($this->anything());
        $this->sut->execute($execution);
    }
}
