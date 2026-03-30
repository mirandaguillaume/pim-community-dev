<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Writer\Database;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Connector\Writer\Database\Writer;

class WriterTest extends TestCase
{
    private BulkSaverInterface|MockObject $bulkSaver;
    private BulkObjectDetacherInterface|MockObject $bulkDetacher;
    private StepExecution|MockObject $stepExecution;
    private Writer $sut;

    protected function setUp(): void
    {
        $this->bulkSaver = $this->createMock(BulkSaverInterface::class);
        $this->bulkDetacher = $this->createMock(BulkObjectDetacherInterface::class);
        $this->stepExecution = $this->createMock(StepExecution::class);
        $this->sut = new Writer($this->bulkSaver, $this->bulkDetacher);
        $this->sut->setStepExecution($this->stepExecution);
    }

    public function test_it_is_a_writer(): void
    {
        $this->assertInstanceOf(ItemWriterInterface::class, $this->sut);
        $this->assertInstanceOf(StepExecutionAwareInterface::class, $this->sut);
    }

    public function test_it_massively_insert_and_update_objects(): void
    {
        $object1 = $this->createMock(CategoryInterface::class);
        $object2 = $this->createMock(CategoryInterface::class);

        $this->bulkSaver->method('saveAll')->with([$object1, $object2]);
        $this->bulkDetacher->method('detachAll')->with([$object1, $object2]);
        $object1->method('getId')->willReturn(null);
        $this->stepExecution->expects($this->once())->method('incrementSummaryInfo')->with('create');
        $object2->method('getId')->willReturn(42);
        $this->stepExecution->expects($this->once())->method('incrementSummaryInfo')->with('update');
        $this->sut->write([$object1, $object2]);
    }
}
