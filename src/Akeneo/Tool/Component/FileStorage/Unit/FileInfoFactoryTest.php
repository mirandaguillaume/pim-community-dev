<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\FileStorage;

use Akeneo\Tool\Component\FileStorage\FileInfoFactory;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\FileStorage\PathGeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileInfoFactoryTest extends TestCase
{
    private PathGeneratorInterface|MockObject $pathGenerator;
    private FileInfoFactory $sut;

    protected function setUp(): void
    {
        $this->pathGenerator = $this->createMock(PathGeneratorInterface::class);
        $this->sut = new FileInfoFactory($this->pathGenerator, FileInfo::class);
    }

    public function test_it_creates_a_file_from_a_raw_file(): void
    {
        $rawFile = new \SplFileInfo(__FILE__);
        $this->pathGenerator->method('generate')->with($rawFile)->willReturn([
                    'uuid'      => '12345',
                    'file_name' => '12345_my_file.php',
                    'path'      => '1/2/3/4/',
                    'path_name' => '1/2/3/4/12345_my_file.php',
                ]);
        $file = $this->sut->createFromRawFile($rawFile, 'destination');
        $this->assertInstanceOf(FileInfo::class, $file);
        $this->assertNotNull($file->getKey());
    }

    public function test_it_creates_a_file_from_an_uploaded_file(): void
    {
        $rawFile = new UploadedFile(__FILE__, 'FileInfoFactorySpec.php', 'text/x-php', filesize(__FILE__));
        $this->pathGenerator->method('generate')->with($rawFile)->willReturn([
                    'uuid'      => '12345',
                    'file_name' => '12345_my_file.php',
                    'path'      => '1/2/3/4/',
                    'path_name' => '1/2/3/4/12345_my_file.php',
                ]);
        $file = $this->sut->createFromRawFile($rawFile, 'destination');
        $this->assertInstanceOf(FileInfo::class, $file);
        $this->assertNotNull($file->getKey());
    }
}
