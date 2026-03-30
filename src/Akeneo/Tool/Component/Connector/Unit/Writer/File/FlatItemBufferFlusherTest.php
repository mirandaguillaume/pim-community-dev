<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Writer\File;

use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Writer\File\ColumnPresenterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\ColumnSorterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class FlatItemBufferFlusherTest extends TestCase
{
    private ColumnSorterInterface|MockObject $columnSorter;
    private StepExecution|MockObject $stepExecution;
    private ColumnPresenterInterface|MockObject $columnPresenter;
    private FlatItemBufferFlusher $sut;

    protected function setUp(): void
    {
        $this->columnSorter = $this->createMock(ColumnSorterInterface::class);
        $this->stepExecution = $this->createMock(StepExecution::class);
        $this->columnPresenter = $this->createMock(ColumnPresenterInterface::class);
        $this->sut = new FlatItemBufferFlusher($this->columnPresenter, $this->columnSorter);
        $this->sut->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'spec' . DIRECTORY_SEPARATOR;
        $this->sut->filesystem = new Filesystem();
        $this->sut->filesystem->mkdir($this->directory);
        $this->columnPresenter->method('present')->with($this->anything(), $this->anything());
        $this->sut->setStepExecution($this->stepExecution);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FlatItemBufferFlusher::class, $this->sut);
    }

    public function test_it_should_not_create_file_if_buffer_is_empty(): void
    {
        $buffer = $this->createMock(FlatItemBuffer::class);

        $buffer->method('count')->willReturn(0);
        $this->assertSame([], $this->sut->flush($buffer, ['type' => 'csv'], $this->directory . 'output'));
    }

    public function test_it_flushes_a_buffer_without_a_max_number_of_lines(): void
    {
        $buffer = $this->createMock(FlatItemBuffer::class);
        $parameters = $this->createMock(JobParameters::class);

        $this->columnSorter->method('sort')->with($this->anything(), [])->willReturn(['colA', 'colB']);
        $this->stepExecution->method('getJobParameters')->willReturn($parameters);
        $this->stepExecution->expects($this->once())->method('incrementSummaryInfo')->with('write');
        $parameters->method('all')->willReturn([]);
        $buffer->method('count')->willReturn(3);
        $buffer->method('key')->willReturn(0);
        $buffer->method('rewind');
        $buffer->method('valid')->willReturn(true, false);
        $buffer->method('next');
        $buffer->method('current')->willReturn(['fooA', 'fooB']);
        $buffer->method('getHeaders')->willReturn(['colA', 'colB']);
        $this->sut->flush($buffer, ['type' => 'csv'], $this->directory . 'output');
        if (!file_exists($this->directory . 'output')) {
            throw new FailedPredictionException(
                sprintf('File "%s" should have been flushed', $this->directory . 'output')
            );
        }
    }

    public function test_it_flushes_a_buffer_into_multiple_files_without_extension(): void
    {
        $buffer = $this->createMock(FlatItemBuffer::class);
        $parameters = $this->createMock(JobParameters::class);

        $this->columnSorter->method('sort')->with($this->anything(), [])->willReturn(['colA', 'colB']);
        $this->stepExecution->method('getJobParameters')->willReturn($parameters);
        $this->stepExecution->expects($this->once())->method('incrementSummaryInfo')->with('write');
        $parameters->method('all')->willReturn([]);
        $buffer->method('rewind');
        $buffer->method('count')->willReturn(3);
        $buffer->method('valid')->willReturn(true, true, true, false);
        $buffer->method('next');
        $buffer->method('current')->willReturn([
                    'colA' => 'fooA',
                    'colB' => 'fooB',
                ]);
        $buffer->method('key')->willReturn(0);
        $buffer->method('getHeaders')->willReturn(['colA', 'colB']);
        $this->sut->flush($buffer, ['type' => 'csv'], $this->directory . 'output', 2);
        if (!file_exists($this->directory . 'output_1')) {
            throw new FailedPredictionException(
                sprintf('File "%s" should have been flushed', $this->directory . 'output_1')
            );
        }
        if (!file_exists($this->directory . 'output_2')) {
            throw new FailedPredictionException(
                sprintf('File "%s" should have been flushed', $this->directory . 'output_2')
            );
        }
    }

    public function test_it_flushes_a_buffer_into_multiple_files_with_extension(): void
    {
        $buffer = $this->createMock(FlatItemBuffer::class);
        $parameters = $this->createMock(JobParameters::class);

        $this->columnSorter->method('sort')->with($this->anything(), [])->willReturn(['colA', 'colB']);
        $this->stepExecution->method('getJobParameters')->willReturn($parameters);
        $this->stepExecution->expects($this->once())->method('incrementSummaryInfo')->with('write');
        $parameters->method('all')->willReturn([]);
        $buffer->method('rewind');
        $buffer->method('count')->willReturn(3);
        $buffer->method('valid')->willReturn(true, true, true, false);
        $buffer->method('next');
        $buffer->method('current')->willReturn([
                    'colA' => 'fooA',
                    'colB' => 'fooB',
                ]);
        $buffer->method('key')->willReturn(0);
        $buffer->method('getHeaders')->willReturn(['colA', 'colB']);
        $this->sut->flush($buffer, ['type' => 'csv'], $this->directory . 'output.txt', 2);
        if (!file_exists($this->directory . 'output_1.txt')) {
            throw new FailedPredictionException(
                sprintf('File "%s" should have been flushed', $this->directory . 'output_1.txt')
            );
        }
        if (!file_exists($this->directory . 'output_2.txt')) {
            throw new FailedPredictionException(
                sprintf('File "%s" should have been flushed', $this->directory . 'output_2.txt')
            );
        }
    }

    public function test_it_throws_an_exception_if_type_is_not_defined(): void
    {
        $buffer = $this->createMock(FlatItemBuffer::class);
        $parameters = $this->createMock(JobParameters::class);

        $this->columnSorter->method('sort')->with($this->anything(), [])->willReturn(['colA', 'colB']);
        $buffer->method('count')->willReturn(1);
        $this->stepExecution->method('getJobParameters')->willReturn($parameters);
        $parameters->method('all')->willReturn([]);
        $buffer->method('getHeaders')->willReturn(['colA', 'colB']);
        $this->expectException('InvalidArgumentException');
        $this->sut->flush($buffer, [], $this->anything());
    }

    public function test_it_throws_an_exception_if_type_is_not_recognized(): void
    {
        $buffer = $this->createMock(FlatItemBuffer::class);
        $parameters = $this->createMock(JobParameters::class);

        $this->columnSorter->method('sort')->with($this->anything(), [])->willReturn(['colA', 'colB']);
        $buffer->method('count')->willReturn(1);
        $this->stepExecution->method('getJobParameters')->willReturn($parameters);
        $parameters->method('all')->willReturn([]);
        $buffer->method('getHeaders')->willReturn(['colA', 'colB']);
        $this->expectException('\InvalidArgumentException');
        $this->sut->flush($buffer, ['type' => 'undefined'], $this->anything());
    }
}
