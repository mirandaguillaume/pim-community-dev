<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Reader\File\Xlsx;

use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Exception\BusinessArrayConversionException;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorFactory;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Connector\Reader\File\Xlsx\Reader;
use Symfony\Component\Validator\ConstraintViolationList;

class ReaderTest extends TestCase
{
    private FileIteratorFactory|MockObject $fileIteratorFactory;
    private ArrayConverterInterface|MockObject $converter;
    private StepExecution|MockObject $stepExecution;
    private Reader $sut;

    protected function setUp(): void
    {
        $this->fileIteratorFactory = $this->createMock(FileIteratorFactory::class);
        $this->converter = $this->createMock(ArrayConverterInterface::class);
        $this->stepExecution = $this->createMock(StepExecution::class);
        $this->sut = new Reader($this->fileIteratorFactory, $this->converter);
        $this->sut->setStepExecution($this->stepExecution);
    }

    public function test_it_returns_the_count_of_item_without_header(): void
    {
        $fileIterator = $this->createMock(FileIteratorInterface::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $filePath = __DIR__ . '/features/Context/fixtures/product_with_carriage_return.xlsx';
        $this->stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobParameters->method('has')->with('storage')->willReturn(true);
        $jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => $filePath]);
        $fileIterator->method('valid')->willReturn(true, true, true, false);
        $fileIterator->method('current')->willReturn(null);
        $fileIterator->expects($this->exactly(3))->method('rewind');
        $fileIterator->expects($this->once())->method('next');
        $this->fileIteratorFactory->method('create')->with($filePath, [])->willReturn($fileIterator);
        $this->sut->initialize();
        /** Expect 2 items, even there is 3 lines because the first one (the header) is ignored */
        $this->totalItems()->shouldReturn(2);
    }

    public function test_it_read_xlsx_file(): void
    {
        $fileIterator = $this->createMock(FileIteratorInterface::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobParameters->method('has')->with('storage')->willReturn(true);
        $jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => $this->initFilePath()]);
        $this->sut->initFileIterator($this->fileIteratorFactory, $fileIterator);
        $consolidatedData = [
                    'sku' => 'SKU-001',
                    'name' => 'door',
                ];
        $this->converter->method('convert')->with($consolidatedData, $this->anything())->willReturn($consolidatedData);
        $this->stepExecution->expects($this->once())->method('incrementSummaryInfo')->with('item_position');
        $this->sut->initialize();
        $this->assertSame($consolidatedData, $this->sut->read());
    }

    public function test_it_skips_an_item_in_case_of_conversion_error(): void
    {
        $fileIterator = $this->createMock(FileIteratorInterface::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut->initStepExecution($this->stepExecution, $jobParameters);
        $this->sut->initFileIterator($this->fileIteratorFactory, $fileIterator);
        $consolidatedData = [
                    'sku' => 'SKU-001',
                    'name' => 'door',
                ];
        $this->converter->method('convert')->with($consolidatedData, $this->anything())->willThrowException(new DataArrayConversionException('message', 0, null, new ConstraintViolationList()));
        $this->stepExecution->expects($this->once())->method('incrementSummaryInfo')->with("skip");
        $this->sut->initialize();
        $this->sut->shouldThrow(InvalidItemFromViolationsException::class)->during('read');
    }

    public function test_it_skips_an_item_in_case_of_business_exception_error(): void
    {
        $fileIterator = $this->createMock(FileIteratorInterface::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut->initStepExecution($this->stepExecution, $jobParameters);
        $this->sut->initFileIterator($this->fileIteratorFactory, $fileIterator);
        $consolidatedData = [
                    'sku' => 'SKU-001',
                    'name' => 'door',
                ];
        $this->converter->method('convert')->with($consolidatedData, $this->anything())->willThrowException(new BusinessArrayConversionException('message', 'messageKey', []));
        $this->sut->initialize();
        $this->sut->shouldThrow(InvalidItemException::class)->during('read');
    }

    public function test_it_fill_blank_column_in_row(): void
    {
        $fileIterator = $this->createMock(FileIteratorInterface::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobParameters->method('has')->with('storage')->willReturn(true);
        $jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => $this->initFilePath()]);
        $this->fileIteratorFactory->method('create')->with($this->initFilePath(), [])->willReturn($fileIterator);
        $fileIterator->method('getHeaders')->willReturn(['sku', 'name', 'description', 'short_description']);
        $fileIterator->expects($this->once())->method('rewind');
        $fileIterator->expects($this->once())->method('next');
        $fileIterator->method('valid')->willReturn(true);
        $fileIterator->method('current')->willReturn([
                    0 => 'SKU-001',
                    2 => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                ]);
        $consolidatedData = [
                    'sku' => 'SKU-001',
                    'name' => '',
                    'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                    'short_description' => '',
                ];
        $this->converter->method('convert')->with($consolidatedData, $this->anything())->willReturn($consolidatedData);
        $this->stepExecution->expects($this->once())->method('incrementSummaryInfo')->with('item_position');
        $this->sut->initialize();
        $this->assertSame($consolidatedData, $this->sut->read());
    }

    private function initXlsData(): array
    {
        return ['SKU-001', 'door',];
    }

    private function initFilePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR
            . DIRECTORY_SEPARATOR . 'features'
            . DIRECTORY_SEPARATOR . 'Context'
            . DIRECTORY_SEPARATOR . 'fixtures'
            . DIRECTORY_SEPARATOR . 'product_with_carriage_return.xlsx';
    }

    private function initStepExecution(Collaborator $stepExecution, $jobParameters): void
    {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->has('storage')->willReturn(true);
        $jobParameters->get('storage')->willReturn(['type' => 'local', 'file_path' => $this->initFilePath()]);
    
        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('item_position')->shouldBeCalled();
    }

    private function initFileIterator(Collaborator $fileIteratorFactory, $fileIterator): void
    {
        $fileIteratorFactory->create($this->initFilePath(), [])->willReturn($fileIterator);
    
        $fileIterator->getHeaders()->willReturn(['sku', 'name']);
        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();
        $fileIterator->valid()->willReturn(true);
    
        $fileIterator->current()->willReturn($this->initXlsData());
    }
}
