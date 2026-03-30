<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use League\Flysystem\FilesystemReader;
use League\Flysystem\UnableToReadFile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\FileStorage\File\FileFetcher;

class FileFetcherTest extends TestCase
{
    private FileFetcher $sut;

    protected function setUp(): void
    {
        $this->sut = new FileFetcher();
    }

    public function test_it_fetches_a_file(): void
    {
        $filesystem = $this->createMock(FilesystemReader::class);

        $filesystem->method('fileExists')->with('path/to/file.txt')->willReturn(true);
        $filesystem->expects($this->once())->method('readStream')->with('path/to/file.txt');
        $rawFile = $this->fetch($filesystem, 'path/to/file.txt');
        $rawFile->shouldBeAnInstanceOf(\SplFileInfo::class);
        $rawPathname = $rawFile->getPathname();
        unlink($rawPathname);
    }

    public function test_it_throws_an_exception_when_the_file_is_not_on_the_filesystem(): void
    {
        $filesystem = $this->createMock(FilesystemReader::class);

        $filesystem->method('fileExists')->with('path/to/file.txt')->willReturn(false);
        $this->expectException(\LogicException::class);

        $this->expectExceptionMessage('The file "path/to/file.txt" is not present on the filesystem.');
        $this->sut->fetch($filesystem, 'path/to/file.txt');
    }

    public function test_it_throws_an_exception_when_the_file_can_not_be_read_on_the_filesystem(): void
    {
        $filesystem = $this->createMock(FilesystemReader::class);

        $filesystem->method('fileExists')->with('path/to/file.txt')->willReturn(true);
        $e = UnableToReadFile::fromLocation('path/to/file.txt');
        $filesystem->method('readStream')->with('path/to/file.txt')->willThrowException($e);
        $this->expectException(FileTransferException::class);
        $this->sut->fetch($filesystem, 'path/to/file.txt');
    }
}
