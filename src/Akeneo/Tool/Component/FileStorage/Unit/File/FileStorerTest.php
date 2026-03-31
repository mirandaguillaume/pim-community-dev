<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\FileStorage\File;

use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\Exception\InvalidFile;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Akeneo\Tool\Component\FileStorage\FileInfoFactoryInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToWriteFile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileStorerTest extends TestCase
{
    private FilesystemProvider|MockObject $filesystemProvider;
    private SaverInterface|MockObject $saver;
    private FileInfoFactoryInterface|MockObject $factory;
    private FileInfoInterface|MockObject $fileInfo;
    private FilesystemOperator|MockObject $filesystem;
    private FileStorer $sut;

    protected function setUp(): void
    {
        $this->filesystemProvider = $this->createMock(FilesystemProvider::class);
        $this->saver = $this->createMock(SaverInterface::class);
        $this->factory = $this->createMock(FileInfoFactoryInterface::class);
        $this->fileInfo = $this->createMock(FileInfoInterface::class);
        $this->filesystem = $this->createMock(FilesystemOperator::class);
        $this->sut = new FileStorer($this->filesystemProvider, $this->saver, $this->factory);
        $this->fileInfo->method('getKey')->willReturn('a/b/c/image.png');
        $this->fileInfo->method('getMimeType')->willReturn('image/png');
        $this->factory->method('createFromRawFile')->with($this->isInstanceOf(\SplFileInfo::class), $this->isType('string'))->willReturn($this->fileInfo);
        $this->filesystemProvider->method('getFilesystem')->with($this->isType('string'))->willReturn($this->filesystem);
    }

    public function test_it_stores_a_raw_file(): void
    {
        $localPathname = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'my file.php';
        touch($localPathname);
        $rawFile = new \SplFileInfo($localPathname);
        $this->filesystem->method('fileExists')->with('a/b/c/image.png')->willReturn(false);
        $this->filesystem->expects($this->once())->method('writeStream');
        $this->saver->expects($this->once())->method('save')->with($this->fileInfo);
        $this->sut->store($rawFile, 'destination');
        $this->assertFileExists($localPathname);
        unlink($localPathname);
    }

    public function test_it_stores_a_raw_file_and_deletes_it_locally(): void
    {
        $localPathname = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'my file.php';
        touch($localPathname);
        $rawFile = new \SplFileInfo($localPathname);
        $this->filesystem->method('fileExists')->with('a/b/c/image.png')->willReturn(false);
        $this->filesystem->expects($this->once())->method('writeStream');
        $this->saver->expects($this->once())->method('save')->with($this->fileInfo);
        $this->sut->store($rawFile, 'destination', true);
        $this->assertFileDoesNotExist($localPathname);
    }

    public function test_it_throws_an_exception_if_the_file_can_not_be_writen_on_the_filesystem(): void
    {
        $rawFile = new \SplFileInfo(__FILE__);
        $this->filesystem->method('fileExists')->with('a/b/c/image.png')->willReturn(false);
        $this->filesystem->method('writeStream')->willThrowException(UnableToWriteFile::atLocation(__FILE__, 'Directory is not writable'));
        $this->saver->expects($this->never())->method('save');
        $this->expectException(FileTransferException::class);
        $this->sut->store($rawFile, 'destination');
    }

    public function test_it_throws_an_exception_if_the_file_already_exists_on_the_filesystem(): void
    {
        $rawFile = new \SplFileInfo(__FILE__);
        $this->filesystem->method('fileExists')->with($this->anything())->willReturn(true);
        $this->filesystem->expects($this->never())->method('writeStream');
        $this->saver->expects($this->never())->method('save');
        $this->expectException(FileTransferException::class);
        $this->sut->store($rawFile, 'destination');
    }

    public function test_it_throws_an_exception_if_the_input_file_is_invalid(): void
    {
        $rawFile = new \SplFileInfo('/that/does/not/exist.jpg');
        $this->expectException(InvalidFile::class);
        $this->sut->store($rawFile, 'destination');
    }
}
