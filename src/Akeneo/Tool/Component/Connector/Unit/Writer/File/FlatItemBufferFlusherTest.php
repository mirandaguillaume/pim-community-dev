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

class FlatItemBufferFlusherTest extends TestCase
{
    private ColumnSorterInterface|MockObject $columnSorter;
    private StepExecution|MockObject $stepExecution;
    private ColumnPresenterInterface|MockObject $columnPresenter;
    private FlatItemBufferFlusher $sut;
    private string $directory;

    protected function setUp(): void
    {
        $this->columnSorter = $this->createMock(ColumnSorterInterface::class);
        $this->stepExecution = $this->createMock(StepExecution::class);
        $this->columnPresenter = $this->createMock(ColumnPresenterInterface::class);
        $this->sut = new FlatItemBufferFlusher($this->columnPresenter, $this->columnSorter);
        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'spec' . DIRECTORY_SEPARATOR;
        @mkdir($this->directory, 0777, true);
        $this->columnPresenter->method('present')->willReturnArgument(0);
        $this->sut->setStepExecution($this->stepExecution);
    }

    protected function tearDown(): void
    {
        // Clean up temp files
        $files = glob($this->directory . '*');
        if ($files) {
            array_map('unlink', $files);
        }
        @rmdir($this->directory);
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

    public function test_it_throws_an_exception_if_type_is_not_defined(): void
    {
        $buffer = $this->createMock(FlatItemBuffer::class);
        $parameters = $this->createMock(JobParameters::class);

        $this->columnSorter->method('sort')->willReturn(['colA', 'colB']);
        $buffer->method('count')->willReturn(1);
        $this->stepExecution->method('getJobParameters')->willReturn($parameters);
        $parameters->method('all')->willReturn([]);
        $buffer->method('getHeaders')->willReturn(['colA', 'colB']);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->flush($buffer, [], $this->directory . 'output');
    }

    public function test_it_throws_an_exception_if_type_is_not_recognized(): void
    {
        $buffer = $this->createMock(FlatItemBuffer::class);
        $parameters = $this->createMock(JobParameters::class);

        $this->columnSorter->method('sort')->willReturn(['colA', 'colB']);
        $buffer->method('count')->willReturn(1);
        $this->stepExecution->method('getJobParameters')->willReturn($parameters);
        $parameters->method('all')->willReturn([]);
        $buffer->method('getHeaders')->willReturn(['colA', 'colB']);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->flush($buffer, ['type' => 'undefined'], $this->directory . 'output');
    }
}
