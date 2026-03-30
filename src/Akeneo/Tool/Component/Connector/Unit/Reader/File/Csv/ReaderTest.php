<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Reader\File\Csv;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorFactory;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Connector\Reader\File\Csv\Reader;
use Symfony\Component\Validator\ConstraintViolationList;

class ReaderTest extends TestCase
{
    private FileIteratorFactory|MockObject $fileIteratorFactory;
    private ArrayConverterInterface|MockObject $converter;
    private StepExecution|MockObject $stepExecution;
    private JobParameters|MockObject $jobParameters;
    private FileIteratorInterface|MockObject $fileIterator;
    private Reader $sut;

    protected function setUp(): void
    {
        $this->fileIteratorFactory = $this->createMock(FileIteratorFactory::class);
        $this->converter = $this->createMock(ArrayConverterInterface::class);
        $this->stepExecution = $this->createMock(StepExecution::class);
        $this->jobParameters = $this->createMock(JobParameters::class);
        $this->fileIterator = $this->createMock(FileIteratorInterface::class);
        $this->sut = new Reader($this->fileIteratorFactory, $this->converter);
        $filePath = $this->getPath() . DIRECTORY_SEPARATOR . 'with_media.csv';
        $this->jobParameters->method('get')->with('enclosure')->willReturn('"');
        $this->jobParameters->method('get')->with('delimiter')->willReturn(';');
        $this->jobParameters->method('has')->with('storage')->willReturn(true);
        $this->jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => $filePath]);
        $readerOptions = [
        'fieldDelimiter' => ';',
        'fieldEnclosure' => '"',
        ];
        $this->fileIteratorFactory->method('create')->with($filePath, ['reader_options' => $readerOptions])->willReturn($this->fileIterator);
        $this->stepExecution->method('getJobParameters')->willReturn($this->jobParameters);
        $this->sut->setStepExecution($this->stepExecution);
        $this->sut->initialize();
    }

    public function test_it_returns_the_count_of_item_without_header(): void
    {
        $this->fileIterator->method('valid')->willReturn(true, true, true, false);
        $this->fileIterator->method('current')->willReturn(null);
        $this->fileIterator->expects($this->once())->method('rewind');
        $this->fileIterator->expects($this->once())->method('next');
        /** Expect 2 items, even there is 3 lines because the first one (the header) is ignored */
        $this->totalItems()->shouldReturn(2);
    }

    public function test_it_reads_csv_file(): void
    {
        $data = [
                    'sku'  => 'SKU-001',
                    'name' => 'door',
                ];
        $this->fileIterator->method('getHeaders')->willReturn(['sku', 'name']);
        $this->fileIterator->expects($this->once())->method('rewind');
        $this->fileIterator->expects($this->once())->method('next');
        $this->fileIterator->method('valid')->willReturn(true);
        $this->fileIterator->method('current')->willReturn($data);
        $this->stepExecution->expects($this->once())->method('incrementSummaryInfo')->with('item_position');
        $this->converter->method('convert')->with($data, $this->anything())->willReturn($data);
        $this->assertSame($data, $this->sut->read());
    }

    public function test_it_skips_an_item_in_case_of_conversion_error(): void
    {
        $data = [
                    'sku'  => 'SKU-001',
                    'name' => 'door',
                ];
        $this->stepExecution->expects($this->once())->method('getSummaryInfo')->with('item_position');
        $this->fileIterator->method('getHeaders')->willReturn(['sku', 'name']);
        $this->fileIterator->expects($this->once())->method('rewind');
        $this->fileIterator->expects($this->once())->method('next');
        $this->fileIterator->method('valid')->willReturn(true);
        $this->fileIterator->method('current')->willReturn($data);
        $this->stepExecution->expects($this->once())->method('incrementSummaryInfo')->with('item_position');
        $this->stepExecution->expects($this->once())->method('incrementSummaryInfo')->with("skip");
        $this->converter->method('convert')->with($data, $this->anything())->willThrowException(new DataArrayConversionException('message', 0, null, new ConstraintViolationList()));
        $this->sut->shouldThrow(InvalidItemFromViolationsException::class)->during('read');
    }

    private function getPath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR
            . DIRECTORY_SEPARATOR . 'features'
            . DIRECTORY_SEPARATOR . 'Context'
            . DIRECTORY_SEPARATOR . 'fixtures';
    }
}
