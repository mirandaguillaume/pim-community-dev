<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Bundle\ImportExportBundle\Infrastructure\StorageClient;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageClientInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\TransferFile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TransferFileTest extends TestCase
{
    private TransferFile $sut;

    protected function setUp(): void
    {
        $this->sut = new TransferFile();
    }

    public function test_it_transfers_file_from_a_source_fs_to_a_destination_fs(): void
    {
        $sourceFilesystem = $this->createMock(StorageClientInterface::class);
        $destinationFilesystem = $this->createMock(StorageClientInterface::class);

        $sourceFilePath = 'my_export.xlsx';
        $destinationFilePath = 'my_export_on_server.xlsx';
        $sourceFilesystem->expects($this->once())->method('fileExists')->with($sourceFilePath)->willReturn(true);
        $sourceStream = fopen('php://memory', 'r');
        $sourceFilesystem->expects($this->once())->method('readStream')->with($sourceFilePath)->willReturn($sourceStream);
        $expectedTmpDestinationFilePath = '.tmp-my_export_on_server.xlsx';
        $destinationFilesystem->expects($this->once())->method('writeStream')->with($expectedTmpDestinationFilePath, $sourceStream);
        $destinationFilesystem->expects($this->once())->method('fileExists')->with($destinationFilePath)->willReturn(false);
        $destinationFilesystem->expects($this->once())->method('move')->with($expectedTmpDestinationFilePath, $destinationFilePath);
        $this->sut->transfer($sourceFilesystem, $destinationFilesystem, $sourceFilePath, $destinationFilePath);
    }

    public function test_it_transfers_file_with_a_dirname_from_a_source_fs_to_a_destination_fs(): void
    {
        $sourceFilesystem = $this->createMock(StorageClientInterface::class);
        $destinationFilesystem = $this->createMock(StorageClientInterface::class);

        $sourceFilePath = 'my_export.xlsx';
        $destinationFilePath = 'exports/test/my_export_on_server.xlsx';
        $sourceFilesystem->expects($this->once())->method('fileExists')->with($sourceFilePath)->willReturn(true);
        $sourceStream = fopen('php://memory', 'r');
        $sourceFilesystem->expects($this->once())->method('readStream')->with($sourceFilePath)->willReturn($sourceStream);
        $expectedTmpDestinationFilePath = 'exports/test/.tmp-my_export_on_server.xlsx';
        $destinationFilesystem->expects($this->once())->method('writeStream')->with($expectedTmpDestinationFilePath, $sourceStream);
        $destinationFilesystem->expects($this->once())->method('fileExists')->with($destinationFilePath)->willReturn(true);
        $destinationFilesystem->expects($this->once())->method('delete')->with($destinationFilePath);
        $destinationFilesystem->expects($this->once())->method('move')->with($expectedTmpDestinationFilePath, $destinationFilePath);
        $this->sut->transfer($sourceFilesystem, $destinationFilesystem, $sourceFilePath, $destinationFilePath);
    }

    public function test_it_throws_exception_when_source_file_does_not_exist(): void
    {
        $sourceFilesystem = $this->createMock(StorageClientInterface::class);
        $destinationFilesystem = $this->createMock(StorageClientInterface::class);

        $sourceFilePath = 'my_export.xlsx';
        $sourceFilesystem->method('fileExists')->with($sourceFilePath)->willReturn(false);
        $this->expectException(\RuntimeException::class);
        $this->sut->transfer($sourceFilesystem, $destinationFilesystem, $sourceFilePath, 'my_export_on_server.xlsx');
    }

    public function test_it_throws_exception_when_unable_to_read_from_storage(): void
    {
        $sourceFilesystem = $this->createMock(StorageClientInterface::class);
        $destinationFilesystem = $this->createMock(StorageClientInterface::class);

        $sourceFilePath = 'my_export.xlsx';
        $sourceFilesystem->expects($this->once())->method('fileExists')->with($sourceFilePath)->willReturn(true);
        $sourceFilesystem->expects($this->once())->method('readStream')->with($sourceFilePath)->willThrowException(new \Exception('Unable to read'));
        $this->expectException(\RuntimeException::class);
        $this->sut->transfer($sourceFilesystem, $destinationFilesystem, $sourceFilePath, 'my_export_on_server.xlsx');
    }
}
