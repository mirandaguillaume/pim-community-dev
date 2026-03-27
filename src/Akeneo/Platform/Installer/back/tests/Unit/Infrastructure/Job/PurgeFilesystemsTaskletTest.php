<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Installer\Unit\Infrastructure\Job;

use Akeneo\Platform\Installer\Infrastructure\FilesystemsPurger\FilesystemPurger;
use Akeneo\Platform\Installer\Infrastructure\Job\PurgeFilesystemsTasklet;
use Akeneo\Tool\Component\Batch\Job\JobStopper;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PurgeFilesystemsTaskletTest extends TestCase
{
    private FilesystemPurger|MockObject $filesystemPurger;
    private FilesystemOperator|MockObject $filesystem1;
    private FilesystemOperator|MockObject $filesystem2;
    private StepExecution|MockObject $stepExecution;
    private JobStopper|MockObject $jobStopper;
    private PurgeFilesystemsTasklet $sut;

    protected function setUp(): void
    {
        $this->filesystemPurger = $this->createMock(FilesystemPurger::class);
        $this->filesystem1 = $this->createMock(FilesystemOperator::class);
        $this->filesystem2 = $this->createMock(FilesystemOperator::class);
        $this->stepExecution = $this->createMock(StepExecution::class);
        $this->jobStopper = $this->createMock(JobStopper::class);
        $this->sut = new PurgeFilesystemsTasklet($this->filesystemPurger, [$this->filesystem1, $this->filesystem2], $this->jobStopper);
        $this->sut->setStepExecution($this->stepExecution);
    }

    public function test_it_is_a_tasklet(): void
    {
        $this->assertInstanceOf(PurgeFilesystemsTasklet::class, $this->sut);
        $this->assertInstanceOf(TaskletInterface::class, $this->sut);
    }

    public function test_it_purge_filesystems(): void
    {
        $this->stepExecution->method('getCurrentState')->willReturn([]);
        $this->jobStopper->method('isPausing')->with($this->stepExecution)->willReturn(false);
        $purged = [];
        $this->filesystemPurger->expects($this->exactly(2))->method('purge')->willReturnCallback(
            function (FilesystemOperator $fs) use (&$purged) {
                $purged[] = $fs;
            },
        );
        $this->sut->execute();
        $this->assertContains($this->filesystem1, $purged);
        $this->assertContains($this->filesystem2, $purged);
    }

    public function test_it_can_be_paused(): void
    {
        $this->stepExecution->method('getCurrentState')->willReturn([]);
        $this->jobStopper->expects($this->atLeast(1))->method('isPausing')->with($this->stepExecution)->willReturnOnConsecutiveCalls(false, true);
        $purged = [];
        $this->filesystemPurger->expects($this->once())->method('purge')->willReturnCallback(
            function (FilesystemOperator $fs) use (&$purged) {
                $purged[] = $fs;
            },
        );
        $this->jobStopper->expects($this->once())->method('pause');
        $this->sut->execute();
        $this->assertContains($this->filesystem1, $purged);
        $this->assertNotContains($this->filesystem2, $purged);
    }

    public function test_it_executes_remaining_filesystems_after_paused_job(): void
    {
        $this->stepExecution->method('getCurrentState')->willReturn(['0']);
        $this->jobStopper->method('isPausing')->with($this->stepExecution)->willReturn(false);
        $purged = [];
        $this->filesystemPurger->expects($this->once())->method('purge')->willReturnCallback(
            function (FilesystemOperator $fs) use (&$purged) {
                $purged[] = $fs;
            },
        );
        $this->sut->execute();
        $this->assertNotContains($this->filesystem1, $purged);
        $this->assertContains($this->filesystem2, $purged);
    }
}
