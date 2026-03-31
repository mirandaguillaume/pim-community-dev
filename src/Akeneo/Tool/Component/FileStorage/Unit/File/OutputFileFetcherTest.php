<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\OutputFileFetcher;
use League\Flysystem\FilesystemReader;
use League\Flysystem\UnableToReadFile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class OutputFileFetcherTest extends TestCase
{
    private string $directory;
    private OutputFileFetcher $sut;

    protected function setUp(): void
    {
        $this->sut = new OutputFileFetcher();
        $this->directory = sys_get_temp_dir() . '/spec_output_file_fetcher/';
        $fs = new Filesystem();
        $fs->mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        if (is_dir($this->directory)) {
            $fs->remove($this->directory);
        }
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
        $filesystem->method('readStream')->with($virtualFilesystemPath)->willReturn(fopen('php://temp', 'r'));
        $this->assertInstanceOf(\SplFileInfo::class, $this->sut->fetch($filesystem, $virtualFilesystemPath, $localFilesystemPath));
        $this->assertFileExists($localFilesystemPath['filePath'] . $localFilesystemPath['filename']);
    }

    public function test_it_fetches_a_file_with_the_same_filename(): void
    {
        $filesystem = $this->createMock(FilesystemReader::class);

        $virtualFilesystemPath = 'virtual/path/file.txt';
        $localFilesystemPath = [
                    'filePath' => $this->directory . 'locale/path/',
                ];
        $filesystem->method('fileExists')->with($virtualFilesystemPath)->willReturn(true);
        $filesystem->method('readStream')->with($virtualFilesystemPath)->willReturn(fopen('php://temp', 'r'));
        $this->assertInstanceOf(\SplFileInfo::class, $this->sut->fetch($filesystem, $virtualFilesystemPath, $localFilesystemPath));
        $this->assertFileExists($localFilesystemPath['filePath'] . 'file.txt');
    }

    public function test_it_throws_an_exception_if_options_filePath_is_not_filled(): void
    {
        $filesystem = $this->createMock(FilesystemReader::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Options "filePath" has to be filled');
        $this->sut->fetch($filesystem, 'path/to/file.txt');
    }

    public function test_it_throws_an_exception_if_options_filePath_is_empty(): void
    {
        $filesystem = $this->createMock(FilesystemReader::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Options "filePath" has to be filled');
        $this->sut->fetch($filesystem, 'path/to/file.txt', [
                    'filePath' => '',
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
