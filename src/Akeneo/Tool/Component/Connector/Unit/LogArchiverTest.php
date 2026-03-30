<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector;

use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use League\Flysystem\FilesystemWriter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Connector\LogArchiver;

class LogArchiverTest extends TestCase
{
    private FilesystemWriter|MockObject $filesystem;
    private LogArchiver $sut;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(FilesystemWriter::class);
        $this->sut = new LogArchiver($this->filesystem);
    }

    public function test_it_sends_log_to_flysystem(): void
    {
        $importInstance = new JobInstance(null, JobInstance::TYPE_IMPORT, 'csv_import');
        $importExecution = (new JobExecution())
                    ->setJobInstance($importInstance)
                    ->setLogFile(__FILE__)
        ;
        $event = new JobExecutionEvent($importExecution);
        $this->filesystem->expects($this->once())->method('writeStream');
        $this->sut->archive($event);
    }
}
