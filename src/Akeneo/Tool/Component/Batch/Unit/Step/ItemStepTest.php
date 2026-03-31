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
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface PausableReader extends ItemReaderInterface, StatefulInterface, FlushableInterface
{
}

interface PausableWriter extends ItemWriterInterface, StatefulInterface, FlushableInterface
{
}

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

        $execution->method('getStatus')->willReturn(new BatchStatus(BatchStatus::STARTING));
        $execution->method('getCurrentState')->willReturn([]);
        $execution->method('isTerminateOnly')->willReturn(false);
        $execution->method('getExitStatus')->willReturn(new ExitStatus(ExitStatus::COMPLETED, ''));

        // reader returns r1, r2, r3, r4, then null
        $this->reader->method('read')
            ->willReturnOnConsecutiveCalls('r1', 'r2', 'r3', 'r4', null);

        // processor processes each item
        $this->processor->method('process')
            ->willReturnCallback(fn ($item) => 'p' . substr($item, 1));

        $this->jobStopper->method('isStopping')->willReturn(false);
        $this->jobStopper->method('isPausing')->willReturn(false);

        $this->writer->expects($this->exactly(2))->method('write');
        $this->writer->expects($this->once())->method('flush');

        $this->sut->execute($execution);
    }

    public function test_it_executes_with_an_invalid_item_during_processing(): void
    {
        $execution = $this->createMock(StepExecution::class);

        $execution->method('getStatus')->willReturn(new BatchStatus(BatchStatus::STARTING));
        $execution->method('getCurrentState')->willReturn([]);
        $execution->method('isTerminateOnly')->willReturn(false);
        $execution->method('getExitStatus')->willReturn(new ExitStatus(ExitStatus::COMPLETED, ''));

        $this->reader->method('read')
            ->willReturnOnConsecutiveCalls('r1', 'r2', 'r3', 'r4', null);

        $this->processor->method('process')
            ->willReturnCallback(function ($item) {
                if ($item === 'r4') {
                    throw new InvalidItemException('my msg', new FileInvalidItem(['r4'], 7));
                }
                return 'p' . substr($item, 1);
            });

        $this->jobStopper->method('isStopping')->willReturn(false);
        $this->jobStopper->method('isPausing')->willReturn(false);

        $this->writer->expects($this->once())->method('write');
        $this->writer->expects($this->once())->method('flush');

        $this->sut->execute($execution);
    }

    public function test_it_not_not_write_item_not_processed(): void
    {
        $execution = $this->createMock(StepExecution::class);

        $execution->method('getStatus')->willReturn(new BatchStatus(BatchStatus::STARTING));
        $execution->method('getCurrentState')->willReturn([]);
        $execution->method('isTerminateOnly')->willReturn(false);
        $execution->method('getExitStatus')->willReturn(new ExitStatus(ExitStatus::COMPLETED, ''));

        $this->reader->method('read')
            ->willReturnOnConsecutiveCalls('r1', 'r2', 'r3', 'r4', null);

        $this->processor->method('process')
            ->willReturnCallback(function ($item) {
                if ($item === 'r2') {
                    return null;
                }
                return 'p' . substr($item, 1);
            });

        $this->jobStopper->method('isStopping')->willReturn(false);
        $this->jobStopper->method('isPausing')->willReturn(false);

        // first batch writes [p1, p3] (r2 was filtered), second batch writes [p4]
        $this->writer->expects($this->exactly(2))->method('write');
        $this->writer->expects($this->once())->method('flush');

        $this->sut->execute($execution);
    }

    public function test_it_stop_if_asked(): void
    {
        $execution = $this->createMock(StepExecution::class);

        $execution->method('getStatus')->willReturn(new BatchStatus(BatchStatus::STARTING));
        $execution->method('getCurrentState')->willReturn([]);
        $execution->method('isTerminateOnly')->willReturn(false);
        $execution->method('getExitStatus')->willReturn(new ExitStatus(ExitStatus::STOPPED, ''));

        // Only 3 items (= batch size), so only one batch
        $this->reader->method('read')
            ->willReturnOnConsecutiveCalls('r1', 'r2', 'r3', null);

        $this->processor->method('process')
            ->willReturnCallback(fn ($item) => 'p' . substr($item, 1));

        // isStopping returns true in post-loop check
        $this->jobStopper->method('isStopping')
            ->willReturnOnConsecutiveCalls(false, true);
        $this->jobStopper->method('isPausing')->willReturn(false);
        $this->jobStopper->expects($this->once())->method('stop');

        $this->writer->expects($this->once())->method('write');
        $this->writer->expects($this->once())->method('flush');

        $this->sut->execute($execution);
    }

    public function test_it_pause_if_asked(): void
    {
        $execution = $this->createMock(StepExecution::class);

        $execution->method('getStatus')->willReturn(new BatchStatus(BatchStatus::STARTING));
        $execution->method('getCurrentState')->willReturn([]);
        $execution->method('isTerminateOnly')->willReturn(false);
        $execution->method('getExitStatus')->willReturn(new ExitStatus(ExitStatus::COMPLETED, ''));

        // Exactly one batch of 3 items
        $this->reader->method('read')
            ->willReturnOnConsecutiveCalls('r1', 'r2', 'r3', null);

        $this->processor->method('process')
            ->willReturnCallback(fn ($item) => 'p' . substr($item, 1));

        $this->jobStopper->method('isStopping')->willReturn(false);
        // isPausing: first call at batch check returns true -> pause and break
        $this->jobStopper->method('isPausing')
            ->willReturnOnConsecutiveCalls(true, true);

        $this->reader->method('getState')->willReturn(['position' => 1]);
        $this->writer->method('getState')->willReturn(['file_path' => '/tmp/file.xslx']);

        $this->jobStopper->expects($this->once())->method('pause');
        // flush should NOT be called because isPausing returns true in post-loop check
        // and allItemsHaveBeenRead is false (we broke out early)
        $this->writer->expects($this->never())->method('flush');
        $this->writer->expects($this->once())->method('write');

        $this->sut->execute($execution);
    }

    public function test_it_resumes_paused_job_with_success(): void
    {
        $execution = $this->createMock(StepExecution::class);

        $execution->method('getStatus')->willReturn(new BatchStatus(BatchStatus::PAUSED));
        $execution->method('getCurrentState')->willReturn([]);
        $execution->method('isTerminateOnly')->willReturn(false);
        $execution->method('getExitStatus')->willReturn(new ExitStatus(ExitStatus::COMPLETED, ''));

        $this->reader->method('read')
            ->willReturnOnConsecutiveCalls('r1', 'r2', 'r3', 'r4', null);

        $this->processor->method('process')
            ->willReturnCallback(fn ($item) => 'p' . substr($item, 1));

        $this->jobStopper->method('isStopping')->willReturn(false);
        $this->jobStopper->method('isPausing')->willReturn(false);

        $this->writer->expects($this->exactly(2))->method('write');
        $this->writer->expects($this->once())->method('flush');

        $this->sut->execute($execution);
    }

    public function test_it_executes_a_job_and_set_as_paused(): void
    {
        $execution = $this->createMock(StepExecution::class);

        $execution->method('getStatus')->willReturn(new BatchStatus(BatchStatus::PAUSED));
        $execution->method('getCurrentState')->willReturn([]);
        $execution->method('isTerminateOnly')->willReturn(false);
        $execution->method('getExitStatus')->willReturn(new ExitStatus(ExitStatus::COMPLETED, ''));

        // Exactly one batch of 3 items
        $this->reader->method('read')
            ->willReturnOnConsecutiveCalls('r1', 'r2', 'r3', null);

        $this->processor->method('process')
            ->willReturnCallback(fn ($item) => 'p' . substr($item, 1));

        $this->jobStopper->method('isStopping')->willReturn(false);
        // isPausing returns true at batch check -> pause and break
        $this->jobStopper->method('isPausing')
            ->willReturnOnConsecutiveCalls(true, true);

        $this->reader->method('getState')->willReturn([]);
        $this->writer->method('getState')->willReturn([]);

        $this->jobStopper->expects($this->once())->method('pause');
        // flush not called because isPausing=true in post-loop and allItemsHaveBeenRead=false
        $this->writer->expects($this->never())->method('flush');

        $this->sut->execute($execution);
    }

    public function test_it_flushes_step_elements_when_job_is_pausing_and_every_items_are_processed(): void
    {
        $execution = $this->createMock(StepExecution::class);

        $execution->method('getStatus')->willReturn(new BatchStatus(BatchStatus::STARTING));
        $execution->method('getCurrentState')->willReturn([]);
        $execution->method('isTerminateOnly')->willReturn(false);
        $execution->method('getExitStatus')->willReturn(new ExitStatus(ExitStatus::COMPLETED, ''));

        // Only 3 items (= batch size), so only one batch
        $this->reader->method('read')
            ->willReturnOnConsecutiveCalls('r1', 'r2', 'r3', null);

        $this->processor->method('process')
            ->willReturnCallback(fn ($item) => 'p' . substr($item, 1));

        $this->jobStopper->method('isStopping')->willReturn(false);
        $this->jobStopper->method('isPausing')->willReturn(false);

        $this->writer->expects($this->once())->method('write');
        $this->writer->expects($this->once())->method('flush');

        $this->sut->execute($execution);
    }
}
