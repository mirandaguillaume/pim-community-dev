<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Archiver\ArchiverInterface;
use Akeneo\Tool\Component\Connector\Archiver\FileWriterArchiver;
use Akeneo\Tool\Component\Connector\Step\TaskletStep;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToReadFile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FileWriterArchiverTest extends TestCase
{
    private FilesystemOperator|MockObject $archivistFilesystem;
    private JobRegistry|MockObject $jobRegistry;
    private FilesystemProvider|MockObject $filesystemProvider;
    private LoggerInterface|MockObject $logger;
    private FileWriterArchiver $sut;

    protected function setUp(): void
    {
        $this->archivistFilesystem = $this->createMock(FilesystemOperator::class);
        $this->jobRegistry = $this->createMock(JobRegistry::class);
        $this->filesystemProvider = $this->createMock(FilesystemProvider::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->sut = new FileWriterArchiver($this->archivistFilesystem, $this->jobRegistry, $this->filesystemProvider, $this->logger);
    }

    public function test_it_is_an_archiver(): void
    {
        $this->assertInstanceOf(ArchiverInterface::class, $this->sut);
    }

    public function test_it_is_a_file_writer_archiver(): void
    {
        $this->assertInstanceOf(FileWriterArchiver::class, $this->sut);
        $this->assertSame('output', $this->sut->getName());
    }

    public function test_it_supports_step_execution_when_the_step_is_an_item_step_with_usable_writer(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(Job::class);
        $step1 = $this->createMock(ItemStep::class);

        $jobInstance->method('getJobName')->willReturn('export_job');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $step1->method('getName')->willReturn('step_1');
        $writer = $this->getUsableWriter();
        $step1->method('getWriter')->willReturn($writer);
        $job->method('getSteps')->willReturn([$step1]);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStepName')->willReturn('step_1');
        $this->jobRegistry->method('get')->with('export_job')->willReturn($job);
        $this->assertSame(true, $this->sut->supports($stepExecution));
    }

    public function test_it_does_not_support_step_execution_when_the_step_is_an_item_step_with_not_usable_writer(): void
    {
        $writer = $this->createMock(ItemWriterInterface::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(Job::class);
        $step1 = $this->createMock(ItemStep::class);

        $jobInstance->method('getJobName')->willReturn('export_job');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $step1->method('getName')->willReturn('step_1');
        $step1->method('getWriter')->willReturn($writer);
        $job->method('getSteps')->willReturn([$step1]);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStepName')->willReturn('step_1');
        $this->jobRegistry->method('get')->with('export_job')->willReturn($job);
        $this->assertSame(false, $this->sut->supports($stepExecution));
    }

    public function test_it_does_not_support_step_execution_when_the_job_does_not_exists(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);

        $jobInstance->method('getJobName')->willReturn('export_job');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $this->jobRegistry->method('get')->with('export_job')->willThrowException(\Exception::class);
        $this->assertSame(false, $this->sut->supports($stepExecution));
    }

    public function test_it_does_not_support_step_execution_when_the_job_implementation_is_not_valid(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(JobInterface::class);

        $jobInstance->method('getJobName')->willReturn('export_job');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $this->jobRegistry->method('get')->with('export_job')->willReturn($job);
        $this->assertSame(false, $this->sut->supports($stepExecution));
    }

    public function test_it_does_not_support_step_execution_when_no_step_is_found_in_the_job(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(Job::class);

        $jobInstance->method('getJobName')->willReturn('export_job');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $job->method('getSteps')->willReturn([]);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStepName')->willReturn('step_1');
        $this->jobRegistry->method('get')->with('export_job')->willReturn($job);
        $this->assertSame(false, $this->sut->supports($stepExecution));
    }

    public function test_it_does_not_support_step_execution_when_more_than_one_step_is_found_in_the_job(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(Job::class);
        $step1 = $this->createMock(ItemStep::class);
        $step2 = $this->createMock(ItemStep::class);

        $jobInstance->method('getJobName')->willReturn('export_job');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $step1->method('getName')->willReturn('a_step');
        $step2->method('getName')->willReturn('a_step');
        $job->method('getSteps')->willReturn([$step1, $step2]);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStepName')->willReturn('a_step');
        $this->jobRegistry->method('get')->with('export_job')->willReturn($job);
        $this->assertSame(false, $this->sut->supports($stepExecution));
    }

    public function test_it_does_not_support_step_execution_when_the_step_is_not_an_item_step(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(Job::class);
        $step1 = $this->createMock(TaskletStep::class);

        $jobInstance->method('getJobName')->willReturn('export_job');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $step1->method('getName')->willReturn('step_1');
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStepName')->willReturn('step_1');
        $this->jobRegistry->method('get')->with('export_job')->willReturn($job);
        $this->assertSame(false, $this->sut->supports($stepExecution));
    }

    public function test_it_archives_written_files(): void
    {
        $catalogFilesystem = $this->createMock(FilesystemOperator::class);
        $localFilesystem = $this->createMock(FilesystemOperator::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(Job::class);
        $step1 = $this->createMock(ItemStep::class);
        $step2 = $this->createMock(ItemStep::class);

        $jobInstance->method('getJobName')->willReturn('export_job');
        $jobInstance->method('getType')->willReturn('export');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $jobExecution->method('getId')->willReturn(1);
        $step1->method('getName')->willReturn('step_1');
        $writer = $this->getUsableWriter();
        $step1->method('getWriter')->willReturn($writer);
        $step2->method('getName')->willReturn('step_2');
        $step2->expects($this->never())->method('getWriter');
        $job->method('getSteps')->willReturn([$step1, $step2]);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStepName')->willReturn('step_1');
        $this->jobRegistry->method('get')->with('export_job')->willReturn($job);
        $writtenFiles = [
                    WrittenFileInfo::fromFileStorage(
                        'a/b/c/file.png',
                        'catalogStorage',
                        'files/my_media.png'
                    ),
                    WrittenFileInfo::fromLocalFile(
                        '/tmp/export.csv',
                        'export.csv',
                    ),
                ];
        $path = '/tmp/export.csv';
        $writer = $this->getUsableWriter($writtenFiles, $path);
        $step1->method('getWriter')->willReturn($writer);
        $this->filesystemProvider->method('getFilesystem')->with('catalogStorage')->willReturn($catalogFilesystem);
        $imageStream = fopen('php://memory', 'r');
        $catalogFilesystem->method('readStream')->with('a/b/c/file.png')->willReturn($imageStream);
        $this->archivistFilesystem->expects($this->once())->method('writeStream')->with('export/export_job/1/output/files/my_media.png', $imageStream);
        $this->filesystemProvider->method('getFilesystem')->with('localFilesystem')->willReturn($localFilesystem);
        $csvStream = fopen('php://memory', 'r');
        $localFilesystem->method('readStream')->with('/tmp/export.csv')->willReturn($csvStream);
        $this->archivistFilesystem->expects($this->once())->method('writeStream')->with('export/export_job/1/output/export.csv', $csvStream);
        $this->sut->archive($stepExecution);
    }

    public function test_it_skips_past_steps(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(Job::class);
        $step1 = $this->createMock(ItemStep::class);
        $step2 = $this->createMock(ItemStep::class);

        $jobInstance->method('getJobName')->willReturn('export_job');
        $jobInstance->method('getType')->willReturn('export');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $jobExecution->method('getId')->willReturn(1);
        $step1->method('getName')->willReturn('step_1');
        $step2->method('getName')->willReturn('step_2');
        $job->method('getSteps')->willReturn([$step1, $step2]);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStepName')->willReturn('step_2');
        $this->jobRegistry->method('get')->with('export_job')->willReturn($job);
        $writer = $this->getUsableWriter();
        $step1->expects($this->never())->method('getWriter');
        $step2->method('getWriter')->willReturn($writer);
        $this->archivistFilesystem->expects($this->never())->method('writeStream')->with($this->anything(), $this->anything());
        $this->sut->archive($stepExecution);
    }

    public function test_it_logs_an_error_when_cannot_fetch_writing_file(): void
    {
        $catalogFilesystem = $this->createMock(FilesystemOperator::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(Job::class);
        $step1 = $this->createMock(ItemStep::class);
        $step2 = $this->createMock(ItemStep::class);

        $jobInstance->method('getJobName')->willReturn('export_job');
        $jobInstance->method('getType')->willReturn('export');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $jobExecution->method('getId')->willReturn(1);
        $step1->method('getName')->willReturn('step_1');
        $writer = $this->getUsableWriter();
        $step1->method('getWriter')->willReturn($writer);
        $step2->method('getName')->willReturn('step_2');
        $step2->expects($this->never())->method('getWriter');
        $job->method('getSteps')->willReturn([$step1, $step2]);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStepName')->willReturn('step_1');
        $this->jobRegistry->method('get')->with('export_job')->willReturn($job);
        $writtenFiles = [
                    WrittenFileInfo::fromFileStorage(
                        'a/b/c/non_existing_file.png',
                        'catalogStorage',
                        'files/non_existing_file.png'
                    ),
                    WrittenFileInfo::fromFileStorage(
                        'a/b/c/file.png',
                        'catalogStorage',
                        'files/my_media.png'
                    ),
                ];
        $writer = $this->getUsableWriter($writtenFiles);
        $step1->method('getWriter')->willReturn($writer);
        $this->filesystemProvider->method('getFilesystem')->with('catalogStorage')->willReturn($catalogFilesystem);
        $catalogFilesystem->method('readStream')->with('a/b/c/non_existing_file.png')->willThrowException(UnableToReadFile::class);
        $imageStream = fopen('php://memory', 'r');
        $catalogFilesystem->method('readStream')->with('a/b/c/file.png')->willReturn($imageStream);
        $this->archivistFilesystem->expects($this->once())->method('writeStream')->with('export/export_job/1/output/files/my_media.png', $imageStream);
        $this->archivistFilesystem->expects($this->never())->method('writeStream')->with('export/export_job/1/output/non_existing_file.csv', $this->isType('resource'));
        $this->logger->expects($this->once())->method('warning')->with(
            'The remote file could not be read from the remote filesystem',
            $this->isType('array')
        );
        $this->sut->archive($stepExecution);
    }

    public function test_it_gets_the_archives_for_a_job_execution(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(Job::class);
        $step1 = $this->createMock(ItemStep::class);

        $jobInstance->method('getJobName')->willReturn('export_job');
        $jobInstance->method('getType')->willReturn('export');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $jobExecution->method('getId')->willReturn(1);
        $step1->method('getName')->willReturn('step_1');
        $writer = $this->getUsableWriter([], '/tmp/export.csv');
        $step1->method('getWriter')->willReturn($writer);
        $job->method('getSteps')->willReturn([$step1]);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStepName')->willReturn('step_1');
        $this->jobRegistry->method('get')->with('export_job')->willReturn($job);
        $jobExecution->method('getStepExecutions')->willReturn([$stepExecution]);
        $this->archivistFilesystem->expects($this->once())->method('listContents')->with('export/export_job/1/output', false)->willReturn(new DirectoryListing(
            [
                            new FileAttributes('export/export_job/1/output/export_1.csv'),
                            new FileAttributes('export/export_job/1/output/export_2.csv'),
                            new FileAttributes('export/export_job/1/output/files/sku1/image.jpg'),
                            new FileAttributes('export/export_job/1/output/files/sku2/media.png'),
                        ]
        ));
        $this->sut->getArchives($jobExecution)->shouldYield([
                    'export_1.csv' => 'export/export_job/1/output/export_1.csv',
                    'export_2.csv' => 'export/export_job/1/output/export_2.csv',
                    'files/sku1/image.jpg' => 'export/export_job/1/output/files/sku1/image.jpg',
                    'files/sku2/media.png' => 'export/export_job/1/output/files/sku2/media.png',
                ]);
    }

    public function test_it_returns_the_archivist_directory_from_job_execution(): void
    {
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);

        $jobInstance->method('getJobName')->willReturn('csv_product_export');
        $jobInstance->method('getType')->willReturn('export');
        $jobExecution->method('getId')->willReturn(14);
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $this->assertSame('export/csv_product_export/14/output', $this->sut->getArchiveDirectoryPath($jobExecution));
    }

    private function getUsableWriter(array $writtenFiles = [], string $path = ''): ArchivableWriterInterface
    {
        return new class($writtenFiles, $path) implements ItemWriterInterface, ArchivableWriterInterface {
            public function __construct(
                private readonly array $writtenFiles,
                private readonly string $path,
            ) {
            }
    
            public function getWrittenFiles(): array
            {
                return $this->writtenFiles;
            }
    
            public function getPath(): string
            {
                return $this->path;
            }
    
            public function write(array $items)
            {
            }
        };
    }
}
