<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use League\Flysystem\FilesystemReader;
use League\Flysystem\UnableToReadFile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\FileStorage\File\StreamedFileFetcher;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class StreamedFileFetcherTest extends TestCase
{
    private StreamedFileFetcher $sut;

    protected function setUp(): void
    {
        $this->sut = new StreamedFileFetcher();
        $this->sut->directory = sys_get_temp_dir() . '/spec/';
        $this->sut->filesystem = new Filesystem();
        $this->sut->filesystem->mkdir($this->directory);
    }

    public function test_it_fetches_a_file(): void
    {
        $filesystem = $this->createMock(FilesystemReader::class);

        $virtualFilesystemPath = $this->directory . 'my file.txt';
        touch($virtualFilesystemPath);
        $fp = fopen($virtualFilesystemPath, 'r');
        $filesystem->method('fileExists')->with($virtualFilesystemPath)->willReturn(true);
        $filesystem->method('readStream')->with($virtualFilesystemPath)->willReturn($fp);
        $this->assertInstanceOf(StreamedFileResponse::class, $this->sut->fetch($filesystem, $virtualFilesystemPath, []));
        fclose($fp);
    }

    public function test_it_throws_an_exception_when_the_file_is_not_on_the_filesystem(): void
    {
        $filesystem = $this->createMock(FilesystemReader::class);

        $filesystem->method('fileExists')->with('path/to/file.txt')->willReturn(false);
        $this->expectException(new FileNotFoundException('path/to/file.txt'));
        $this->sut->fetch($filesystem, 'path/to/file.txt', []);
    }

    public function test_it_throws_an_exception_when_the_file_can_not_be_read_on_the_filesystem(): void
    {
        $filesystem = $this->createMock(FilesystemReader::class);

        $filesystem->method('fileExists')->with('path/to/file.txt')->willReturn(true);
        $e = UnableToReadFile::fromLocation('path/to/file.txt');
        $filesystem->method('readStream')->with('path/to/file.txt')->willThrowException($e);
        $this->expectException(new FileTransferException('Unable to fetch the file "path/to/file.txt" from the filesystem.'));
        $this->sut->fetch($filesystem, 'path/to/file.txt', []);
    }
}
