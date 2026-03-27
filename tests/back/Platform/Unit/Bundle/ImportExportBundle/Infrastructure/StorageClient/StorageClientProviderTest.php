<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Bundle\ImportExportBundle\Infrastructure\StorageClient;

use Akeneo\Platform\Bundle\ImportExportBundle\Application\TransferFilesToStorage\FileToTransfer;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\StorageClientInterface;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\StorageClientProvider;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\StorageClient\StorageClientProviderInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StorageClientProviderTest extends TestCase
{
    private FilesystemProvider|MockObject $filesystemProvider;
    private StorageClientProviderInterface|MockObject $firstClientProvider;
    private StorageClientProviderInterface|MockObject $secondClientProvider;
    private StorageClientProvider $sut;

    protected function setUp(): void
    {
        $this->filesystemProvider = $this->createMock(FilesystemProvider::class);
        $this->firstClientProvider = $this->createMock(StorageClientProviderInterface::class);
        $this->secondClientProvider = $this->createMock(StorageClientProviderInterface::class);
        $this->sut = new StorageClientProvider($this->filesystemProvider, [$this->firstClientProvider, $this->secondClientProvider]);
    }

    public function test_it_returns_storage_client_from_file_to_transfer(): void
    {
        $filesystemOperator = $this->createMock(FilesystemOperator::class);

        $this->filesystemProvider->method('getFilesystem')->with('local')->willReturn($filesystemOperator);
        $fileToTransfer = new FileToTransfer('fileKey', 'local', 'outputFileName', false);
        $result = $this->sut->getFromFileToTransfer($fileToTransfer);
        $this->assertInstanceOf(StorageClientInterface::class, $result);
    }

    public function test_it_returns_first_client_provider_that_support_storage_configuration(): void
    {
        $secondClient = $this->createMock(StorageClientInterface::class);
        $secondStorage = $this->createMock(StorageInterface::class);

        $this->firstClientProvider->method('supports')->with($secondStorage)->willReturn(false);
        $this->secondClientProvider->method('supports')->with($secondStorage)->willReturn(true);
        $this->secondClientProvider->expects($this->once())->method('getFromStorage')->with($secondStorage)->willReturn($secondClient);
        $this->assertSame($secondClient, $this->sut->getFromStorage($secondStorage));
    }

    public function test_it_throws_an_exception_when_no_client_provider_support_the_storage_configuration(): void
    {
        $storage = $this->createMock(StorageInterface::class);

        $this->firstClientProvider->method('supports')->with($storage)->willReturn(false);
        $this->secondClientProvider->method('supports')->with($storage)->willReturn(false);
        $this->expectException(\RuntimeException::class);
        $this->sut->getFromStorage($storage);
    }
}
