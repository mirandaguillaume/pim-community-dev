<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Writer\Database;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Writer\Database\JobInstanceWriter;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JobInstanceWriterTest extends TestCase
{
    private BulkSaverInterface|MockObject $bulkSaver;
    private BulkObjectDetacherInterface|MockObject $bulkDetacher;
    private StepExecution|MockObject $stepExecution;
    private JobInstanceWriter $sut;

    protected function setUp(): void
    {
        $this->bulkSaver = $this->createMock(BulkSaverInterface::class);
        $this->bulkDetacher = $this->createMock(BulkObjectDetacherInterface::class);
        $this->stepExecution = $this->createMock(StepExecution::class);
        $this->sut = new JobInstanceWriter($this->bulkSaver, $this->bulkDetacher);
        $this->sut->setStepExecution($this->stepExecution);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(JobInstanceWriter::class, $this->sut);
    }

    public function test_it_is_a_writer(): void
    {
        $this->assertInstanceOf(ItemWriterInterface::class, $this->sut);
        $this->assertInstanceOf(StepExecutionAwareInterface::class, $this->sut);
    }

    public function test_it_saves_and_updates_job_instances(): void
    {
        $jobInstance1 = $this->createMock(JobInstance::class);
        $jobInstance2 = $this->createMock(JobInstance::class);

        $this->bulkSaver->expects($this->once())->method('saveAll')->with([$jobInstance1, $jobInstance2]);
        $this->bulkDetacher->expects($this->once())->method('detachAll')->with([$jobInstance1, $jobInstance2]);
        $jobInstance1->method('getId')->willReturn(null);
        $jobInstance2->method('getId')->willReturn(42);
        $this->stepExecution->expects($this->exactly(2))->method('incrementSummaryInfo');
        $this->sut->write([$jobInstance1, $jobInstance2]);
    }
}
