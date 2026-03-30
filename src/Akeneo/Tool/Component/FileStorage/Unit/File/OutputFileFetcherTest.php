<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use League\Flysystem\FilesystemReader;
use League\Flysystem\UnableToReadFile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\FileStorage\File\OutputFileFetcher;
use Symfony\Component\Filesystem\Filesystem;

class OutputFileFetcherTest extends TestCase
{
    private OutputFileFetcher $sut;

    protected function setUp(): void
    {
        $this->sut = new OutputFileFetcher();
        $this->sut->directory = sys_get_temp_dir() . '/spec/';
        $this->sut->filesystem = new Filesystem();
        $this->sut->filesystem->mkdir($this->directory);
    }

    public function test_it_fetches_a_file(): void
    {
        $filesystem = $this->createMock(FilesystemReader::class);

        $virtualFilesystemPath = 'virtual/path/file.txt';
        $localFilesystemPath = [
                    'filePath' => $this->directory . 'locale/path/',
                    'filename' => 'filename.txt',
                ];
        $filesystem->method('fileExists')->with($virtualFilesystemPath)->willReturn(true);
        $filesystem->expects($this->once())->method('readStream')->with($virtualFilesystemPath);
        $this->assertInstanceOf(\SplFileInfo::class, $this->sut->fetch($filesystem, $virtualFilesystemPath, $localFilesystemPath));
        if (!file_exists($localFilesystemPath['filePath'] . $localFilesystemPath['filename'])) {
            throw new FailedPredictionException(
                sprintf('File "%s" should have been created', $localFilesystemPath['filename'])
            );
        }
    }

    public function test_it_fetches_a_file_with_the_same_filename(): void
    {
        $filesystem = $this->createMock(FilesystemReader::class);

        $virtualFilesystemPath = 'virtual/path/file.txt';
        $localFilesystemPath = [
                    'filePath' => $this->directory . 'locale/path/',
                ];
        $filesystem->method('fileExists')->with($virtualFilesystemPath)->willReturn(true);
        $filesystem->expects($this->once())->method('readStream')->with($virtualFilesystemPath);
        $this->assertInstanceOf('\SplFileInfo', $this->sut->fetch($filesystem, $virtualFilesystemPath, $localFilesystemPath));
        if (!file_exists($localFilesystemPath['filePath'] . 'file.txt')) {
            throw new FailedPredictionException('File file.txt" should have been created');
        }
    }

    public function test_it_throws_an_exception_if_options_directory_or_filename_are_not_filled(): void
    {
        $filesystem = $this->createMock(FilesystemReader::class);

        $this->expectException(\LogicException::class);


        $this->expectExceptionMessage('Options "filePath" has to be filled');
        $this->sut->fetch($filesystem, 'path/to/file.txt');
        $this->expectException(\LogicException::class);

        $this->expectExceptionMessage('Options "filePath" has to be filled');
        $this->sut->fetch($filesystem, 'path/to/file.txt', [
                    'filePath' => '',
                ]);
        $this->expectException(\LogicException::class);

        $this->expectExceptionMessage('Options "filePath" has to be filled');
        $this->sut->fetch($filesystem, 'path/to/file.txt', [
                    'filePath' => null,
                ]);
    }

    public function test_it_throws_an_exception_when_the_file_is_not_on_the_filesystem(): void
    {
        $filesystem = $this->createMock(FilesystemReader::class);

        $filesystem->method('fileExists')->with('path/to/file.txt')->willReturn(false);
        $this->expectException(\LogicException::class);

        $this->expectExceptionMessage('The file "path/to/file.txt" is not present on the filesystem.');
        $this->sut->fetch($filesystem, 'path/to/file.txt', [
                    'filePath' => 'locale/path/filename.txt',
                ]);
    }

    public function test_it_throws_an_exception_when_the_file_can_not_be_read_on_the_filesystem(): void
    {
        $filesystem = $this->createMock(FilesystemReader::class);

        $filesystem->method('fileExists')->with('path/to/file.txt')->willReturn(true);
        $filesystem->method('readStream')->with('path/to/file.txt')->willThrowException(UnableToReadFile::fromLocation('path/to/file.txt', 'Directory is not readable'));
        $this->expectException(FileTransferException::class);

        $this->expectExceptionMessage('Unable to fetch the file "path/to/file.txt" from the filesystem.');
        $this->sut->fetch($filesystem, 'path/to/file.txt', [
                    'filePath' => 'locale/path/filename.txt',
                ]);
    }
}
