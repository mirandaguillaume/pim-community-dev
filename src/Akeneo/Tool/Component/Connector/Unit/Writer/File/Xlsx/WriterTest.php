<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Writer\File\Xlsx;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Job\JobFileBackuper;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Akeneo\Tool\Component\Connector\Writer\File\Xlsx\Writer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WriterTest extends TestCase
{
    private ArrayConverterInterface|MockObject $arrayConverter;
    private BufferFactory|MockObject $bufferFactory;
    private FlatItemBufferFlusher|MockObject $flusher;
    private JobFileBackuper|MockObject $jobFileBackuper;
    private Writer $sut;

    protected function setUp(): void
    {
        $this->arrayConverter = $this->createMock(ArrayConverterInterface::class);
        $this->bufferFactory = $this->createMock(BufferFactory::class);
        $this->flusher = $this->createMock(FlatItemBufferFlusher::class);
        $this->jobFileBackuper = $this->createMock(JobFileBackuper::class);
        $this->sut = new Writer($this->arrayConverter, $this->bufferFactory, $this->flusher, $this->jobFileBackuper);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(Writer::class, $this->sut);
    }

    public function test_it_is_step_execution_aware(): void
    {
        $this->assertInstanceOf(StepExecutionAwareInterface::class, $this->sut);
    }

    public function test_it_is_a_writer(): void
    {
        $this->assertInstanceOf(ItemWriterInterface::class, $this->sut);
    }

    public function test_it_prepares_items_to_write(): void
    {
        $flatRowBuffer = $this->createMock(FlatItemBuffer::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut->setStepExecution($stepExecution);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStartTime')->willReturn(new \DateTimeImmutable('1967-08-05 15:15:00'));
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $jobInstance->method('getLabel')->willReturn('XLSX Group export');
        $jobParameters->method('get')->with('withHeader')->willReturn(true);
        $jobParameters->method('has')->with('storage')->willReturn(true);
        $jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => sys_get_temp_dir() . '/my/file/path/%job_label%_%datetime%.xlsx']);
        $jobParameters->method('has')->with('ui_locale')->willReturn(false);
        $groups = [
                    [
                        'code'   => 'promotion',
                        'type'   => 'RELATED',
                        'labels' => ['en_US' => 'Promotion', 'de_DE' => 'Förderung'],
                    ],
                    [
                        'code'   => 'related',
                        'type'   => 'RELATED',
                        'labels' => ['en_US' => 'Related', 'de_DE' => 'Verbunden'],
                    ],
                ];
        $this->arrayConverter->method('convert')->with([
                    'code'   => 'promotion',
                    'type'   => 'RELATED',
                    'labels' => ['en_US' => 'Promotion', 'de_DE' => 'Förderung'],
                ])->willReturn([
                    'code'        => 'promotion',
                    'type'        => 'RELATED',
                    'label-en_US' => 'Promotion',
                    'label-de_DE' => 'Förderung',
                ]);
        $this->arrayConverter->method('convert')->with([
                    'code'   => 'related',
                    'type'   => 'RELATED',
                    'labels' => ['en_US' => 'Related', 'de_DE' => 'Verbunden'],
                ])->willReturn([
                    'code'        => 'related',
                    'type'        => 'RELATED',
                    'label-en_US' => 'Related',
                    'label-de_DE' => 'Verbunden',
                ]);
        $this->bufferFactory->method('create')->with(null)->willReturn($flatRowBuffer);
        $flatRowBuffer->expects($this->once())->method('write')->with(
            [
                        [
                            'code'        => 'promotion',
                            'type'        => 'RELATED',
                            'label-en_US' => 'Promotion',
                            'label-de_DE' => 'Förderung',
                        ],
                        [
                            'code'        => 'related',
                            'type'        => 'RELATED',
                            'label-en_US' => 'Related',
                            'label-de_DE' => 'Verbunden',
                        ],
                    ],
            ['withHeader' => true]
        );
        $this->sut->initialize();
        $this->sut->write($groups);
    }

    public function test_it_writes_the_xlsx_file(): void
    {
        $flatRowBuffer = $this->createMock(FlatItemBuffer::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $this->sut->setStepExecution($stepExecution);
        $this->flusher->expects($this->once())->method('setStepExecution')->with($stepExecution);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStartTime')->willReturn(new \DateTimeImmutable('1967-08-05 15:15:00'));
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $jobInstance->method('getLabel')->willReturn('XLSX Group export');
        $jobParameters->method('get')->with('linesPerFile')->willReturn(1);
        $jobParameters->method('has')->with('storage')->willReturn(true);
        $jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => sys_get_temp_dir() . '/my/file/path/%job_label%_%datetime%.xlsx']);
        $jobParameters->method('has')->with('ui_locale')->willReturn(false);
        $this->bufferFactory->method('create')->with(null)->willReturn($flatRowBuffer);
        $this->sut->initialize();
        $this->flusher->method('flush')->with(
            $flatRowBuffer,
            ['type' => 'xlsx'],
            sys_get_temp_dir() . '/my/file/path/XLSX_Group_export_1967-08-05_15-15-00.xlsx',
            1
        )->willReturn([
                        sys_get_temp_dir() . '/my/file/path/XLSX_Group_export_1967-08-05_15-15-00_1.xlsx',
                        sys_get_temp_dir() . '/my/file/path/XLSX_Group_export_1967-08-05_15-15-00_2.xlsx',
                    ]);
        $this->sut->flush();
        $this->assertEquals([
                        WrittenFileInfo::fromLocalFile(
                            sys_get_temp_dir() . '/my/file/path/XLSX_Group_export_1967-08-05_15-15-00_1.xlsx',
                            'XLSX_Group_export_1967-08-05_15-15-00_1.xlsx'
                        ),
                        WrittenFileInfo::fromLocalFile(
                            sys_get_temp_dir() . '/my/file/path/XLSX_Group_export_1967-08-05_15-15-00_2.xlsx',
                            'XLSX_Group_export_1967-08-05_15-15-00_2.xlsx'
                        ),
                    ], $this->sut->getWrittenFiles());
    }
}
