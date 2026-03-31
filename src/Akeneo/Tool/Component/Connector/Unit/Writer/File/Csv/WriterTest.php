<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Writer\File\Csv;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Job\JobFileBackuper;
use Akeneo\Tool\Component\Connector\Writer\File\Csv\Writer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBufferFlusher;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
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

    public function test_it_is_a_writer(): void
    {
        $this->assertInstanceOf(ItemWriterInterface::class, $this->sut);
    }

    public function test_it_prepares_the_export(): void
    {
        $flatRowBuffer = $this->createMock(FlatItemBuffer::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);

        $this->sut->setStepExecution($stepExecution);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStartTime')->willReturn(new \DateTimeImmutable('1967-08-05 15:15:00'));
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $jobInstance->method('getLabel')->willReturn('job_label');
        $jobParameters->method('has')->willReturnCallback(fn (string $key) => match ($key) {
            'storage' => true,
            'ui_locale' => false,
            default => false,
        });
        $jobParameters->method('get')->willReturnCallback(fn (string $key) => match ($key) {
            'storage' => ['type' => 'local', 'file_path' => sys_get_temp_dir() . '/my/file/path/%job_label%_%datetime%.csv'],
            'withHeader' => true,
            default => null,
        });
        $this->bufferFactory->method('create')->willReturn($flatRowBuffer);
        $this->arrayConverter->method('convert')->willReturnCallback(function (array $item) {
            if ($item['code'] === 'promotion') {
                return [
                    'code'        => 'promotion',
                    'type'        => 'RELATED',
                    'label-en_US' => 'Promotion',
                    'label-de_DE' => 'Förderung',
                ];
            }
            return [
                'code'        => 'related',
                'type'        => 'RELATED',
                'label-en_US' => 'Related',
                'label-de_DE' => 'Verbunden',
            ];
        });
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
        $flatRowBuffer->expects($this->once())->method('write');
        $this->sut->initialize();
        $this->sut->write($groups);
    }

    public function test_it_writes_the_csv_file(): void
    {
        $flatRowBuffer = $this->createMock(FlatItemBuffer::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);

        $this->sut->setStepExecution($stepExecution);
        $this->flusher->expects($this->once())->method('setStepExecution')->with($stepExecution);
        $stepExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobParameters->method('has')->willReturnCallback(fn (string $key) => match ($key) {
            'linesPerFile' => false,
            'storage' => true,
            'ui_locale' => false,
            default => false,
        });
        $jobParameters->method('get')->willReturnCallback(fn (string $key) => match ($key) {
            'delimiter' => ';',
            'enclosure' => '"',
            'storage' => ['type' => 'local', 'file_path' => sys_get_temp_dir() . '/my/file/path/%job_label%_%datetime%.csv'],
            default => null,
        });
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStartTime')->willReturn(new \DateTimeImmutable('1967-08-05 15:15:00'));
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $jobInstance->method('getLabel')->willReturn('job_label');
        $this->bufferFactory->method('create')->willReturn($flatRowBuffer);
        $this->sut->initialize();
        $this->flusher->method('flush')->willReturn([
            sys_get_temp_dir() . '/my/file/path/job_label_1967-08-05_15-15-00.csv',
        ]);
        $this->sut->flush();
        $this->assertEquals([
            WrittenFileInfo::fromLocalFile(
                sys_get_temp_dir() . '/my/file/path/job_label_1967-08-05_15-15-00.csv',
                'job_label_1967-08-05_15-15-00.csv'
            ),
        ], $this->sut->getWrittenFiles());
    }
}
