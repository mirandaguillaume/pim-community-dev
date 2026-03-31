<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Archiver\FileReaderArchiver;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;
use Akeneo\Tool\Component\Connector\Step\TaskletStep;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileReaderArchiverTest extends TestCase
{
    private FilesystemOperator|MockObject $localFilesystem;
    private FilesystemOperator|MockObject $archivistFilesystem;
    private JobRegistry|MockObject $jobRegistry;
    private FileReaderArchiver $sut;

    protected function setUp(): void
    {
        $this->localFilesystem = $this->createMock(FilesystemOperator::class);
        $this->archivistFilesystem = $this->createMock(FilesystemOperator::class);
        $this->jobRegistry = $this->createMock(JobRegistry::class);
        $this->sut = new FileReaderArchiver($this->localFilesystem, $this->archivistFilesystem, $this->jobRegistry);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FileReaderArchiver::class, $this->sut);
    }

    public function test_it_returns_the_name_of_the_archiver(): void
    {
        $this->assertSame('input', $this->sut->getName());
    }

    public function test_it_archives_a_file_when_reader_is_valid(): void
    {
        $reader = $this->createMock(FileReaderInterface::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(Job::class);
        $step1 = $this->createMock(ItemStep::class);
        $step2 = $this->createMock(ItemStep::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $jobInstance->method('getJobName')->willReturn('import_job');
        $jobInstance->method('getType')->willReturn('import');
        $jobParameters->method('has')->with('storage')->willReturn(true);
        $jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => '/tmp/import.xlsx']);
        $jobExecution->method('getId')->willReturn(1);
        $jobExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStepName')->willReturn('step_1');
        $step1->method('getName')->willReturn('step_1');
        $step1->method('getReader')->willReturn($reader);
        $step2->method('getName')->willReturn('step_2');
        $step2->expects($this->never())->method('getReader');
        $job->method('getSteps')->willReturn([$step1, $step2]);
        $this->jobRegistry->method('get')->with('import_job')->willReturn($job);
        $expectedStream = fopen('php://memory', 'r');
        $this->localFilesystem->method('fileExists')->with('/tmp/import.xlsx')->willReturn(true);
        $this->localFilesystem->method('readStream')->with('/tmp/import.xlsx')->willReturn($expectedStream);
        $expectedFilePath = 'import/import_job/1/input/import.xlsx';
        $this->archivistFilesystem->expects($this->once())->method('writeStream')->with($expectedFilePath, $expectedStream);
        $this->sut->archive($stepExecution);
    }

    public function test_it_does_not_archive_a_file_if_step_is_not_an_item_step(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(Job::class);
        $step1 = $this->createMock(TaskletStep::class);

        $jobInstance->method('getJobName')->willReturn('import_job');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $step1->method('getName')->willReturn('step_1');
        $job->method('getSteps')->willReturn([$step1]);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStepName')->willReturn('step_1');
        $this->jobRegistry->method('get')->with('import_job')->willReturn($job);
        $this->archivistFilesystem->expects($this->never())->method('writeStream')->with($this->anything(), $this->anything());
        $this->sut->archive($stepExecution);
    }

    public function test_it_does_not_archive_a_file_when_reader_is_invalid(): void
    {
        $reader = $this->createMock(ItemReaderInterface::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(Job::class);
        $step1 = $this->createMock(ItemStep::class);

        $jobInstance->method('getJobName')->willReturn('import_job');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $step1->method('getName')->willReturn('step_1');
        $step1->method('getReader')->willReturn($reader);
        $job->method('getSteps')->willReturn([$step1]);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStepName')->willReturn('step_1');
        $this->jobRegistry->method('get')->with('import_job')->willReturn($job);
        $this->archivistFilesystem->expects($this->never())->method('writeStream')->with($this->anything(), $this->anything());
        $this->sut->archive($stepExecution);
    }

    public function test_it_skips_past_steps(): void
    {
        $reader = $this->createMock(FileReaderInterface::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(Job::class);
        $step1 = $this->createMock(ItemStep::class);
        $step2 = $this->createMock(ItemStep::class);
        $jobParameters = $this->createMock(JobParameters::class);

        $jobInstance->method('getJobName')->willReturn('import_job');
        $jobInstance->method('getType')->willReturn('import');
        $jobParameters->method('has')->with('storage')->willReturn(true);
        $jobParameters->method('get')->with('storage')->willReturn(['type' => 'local', 'file_path' => '/tmp/import.xlsx']);
        $jobExecution->method('getId')->willReturn(1);
        $jobExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStepName')->willReturn('step_2');
        $step1->method('getName')->willReturn('step_1');
        $step1->expects($this->never())->method('getReader');
        $step2->method('getName')->willReturn('step_2');
        $step2->method('getReader')->willReturn($reader);
        $job->method('getSteps')->willReturn([$step1, $step2]);
        $this->jobRegistry->method('get')->with('import_job')->willReturn($job);
        $expectedStream = fopen('php://memory', 'r');
        $this->localFilesystem->method('fileExists')->with('/tmp/import.xlsx')->willReturn(true);
        $this->localFilesystem->method('readStream')->with('/tmp/import.xlsx')->willReturn($expectedStream);
        $expectedFilePath = 'import/import_job/1/input/import.xlsx';
        $this->archivistFilesystem->expects($this->once())->method('writeStream')->with($expectedFilePath, $expectedStream);
        $this->sut->archive($stepExecution);
    }

    public function test_it_supports_step_execution_when_the_step_is_an_item_step_with_usable_reader(): void
    {
        $reader = $this->createMock(FileReaderInterface::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(Job::class);
        $step1 = $this->createMock(ItemStep::class);

        $jobInstance->method('getJobName')->willReturn('import_job');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $step1->method('getName')->willReturn('step_1');
        $step1->method('getReader')->willReturn($reader);
        $job->method('getSteps')->willReturn([$step1]);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStepName')->willReturn('step_1');
        $this->jobRegistry->method('get')->with('import_job')->willReturn($job);
        $this->assertSame(true, $this->sut->supports($stepExecution));
    }

    public function test_it_does_not_support_step_execution_when_the_step_is_not_an_item_step(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(Job::class);
        $step1 = $this->createMock(TaskletStep::class);

        $jobInstance->method('getJobName')->willReturn('import_job');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $step1->method('getName')->willReturn('step_1');
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStepName')->willReturn('step_1');
        $this->jobRegistry->method('get')->with('import_job')->willReturn($job);
        $this->assertSame(false, $this->sut->supports($stepExecution));
    }

    public function test_it_does_not_support_step_execution_when_the_step_is_an_item_step_with_not_usable_reader(): void
    {
        $reader = $this->createMock(ItemReaderInterface::class);
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(Job::class);
        $step1 = $this->createMock(ItemStep::class);

        $jobInstance->method('getJobName')->willReturn('import_job');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $step1->method('getName')->willReturn('step_1');
        $step1->method('getReader')->willReturn($reader);
        $job->method('getSteps')->willReturn([$step1]);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStepName')->willReturn('step_1');
        $this->jobRegistry->method('get')->with('import_job')->willReturn($job);
        $this->assertSame(false, $this->sut->supports($stepExecution));
    }

    public function test_it_does_not_support_step_execution_when_the_job_does_not_exists(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);

        $jobInstance->method('getJobName')->willReturn('import_job');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $this->jobRegistry->method('get')->with('import_job')->willThrowException(new \Exception());
        $this->assertSame(false, $this->sut->supports($stepExecution));
    }

    public function test_it_does_not_support_step_execution_when_the_job_implementation_is_not_valid(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(JobInterface::class);

        $jobInstance->method('getJobName')->willReturn('import_job');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $this->jobRegistry->method('get')->with('import_job')->willReturn($job);
        $this->assertSame(false, $this->sut->supports($stepExecution));
    }

    public function test_it_does_not_support_step_execution_when_no_step_is_found_in_the_job(): void
    {
        $stepExecution = $this->createMock(StepExecution::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobInstance = $this->createMock(JobInstance::class);
        $job = $this->createMock(Job::class);

        $jobInstance->method('getJobName')->willReturn('import_job');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $job->method('getSteps')->willReturn([]);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStepName')->willReturn('step_1');
        $this->jobRegistry->method('get')->with('import_job')->willReturn($job);
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

        $jobInstance->method('getJobName')->willReturn('import_job');
        $jobExecution->method('getJobInstance')->willReturn($jobInstance);
        $step1->method('getName')->willReturn('a_step');
        $step2->method('getName')->willReturn('a_step');
        $job->method('getSteps')->willReturn([$step1, $step2]);
        $stepExecution->method('getJobExecution')->willReturn($jobExecution);
        $stepExecution->method('getStepName')->willReturn('a_step');
        $this->jobRegistry->method('get')->with('import_job')->willReturn($job);
        $this->assertSame(false, $this->sut->supports($stepExecution));
    }
}
