<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Reader\Database;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\Reader\Database\Reader;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    private ObjectRepository|MockObject $repository;
    private Reader $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ObjectRepository::class);
        $this->sut = new Reader($this->repository);
    }

    public function test_it_is_a_reader(): void
    {
        $this->assertInstanceOf(ItemReaderInterface::class, $this->sut);
        $this->assertInstanceOf(StepExecutionAwareInterface::class, $this->sut);
    }

    public function test_it_returns_a_variation(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $stepExecution = $this->createMock(StepExecution::class);

        $this->repository->method('findAll')->willReturn([$product]);
        $this->sut->setStepExecution($stepExecution);
        $stepExecution->expects($this->exactly(1))->method('incrementSummaryInfo')->with('read');
        $this->sut->initialize();
        $this->assertSame($product, $this->sut->read());
        $this->assertNull($this->sut->read());
    }

    public function test_it_returns_the_total_of_items_to_read(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $this->repository->method('findAll')->willReturn([$product, $product]);
        $this->assertSame(2, $this->sut->totalItems());
    }
}
