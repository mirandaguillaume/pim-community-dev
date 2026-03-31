<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\FileStorage;

use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilesystemProviderTest extends TestCase
{
    private FilesystemOperator|MockObject $filesystem1;
    private FilesystemOperator|MockObject $filesystem2;
    private FilesystemProvider $sut;

    protected function setUp(): void
    {
        $this->filesystem1 = $this->createMock(FilesystemOperator::class);
        $this->filesystem2 = $this->createMock(FilesystemOperator::class);
        $this->sut = new FilesystemProvider([
                'foo' => $this->filesystem1,
                'bar' => $this->filesystem2,
            ]);
    }

    public function test_it_gets_the_filesystem(): void
    {
        $this->assertSame($this->filesystem1, $this->sut->getFilesystem('foo'));
        $this->assertSame($this->filesystem2, $this->sut->getFilesystem('bar'));
    }

    public function test_it_throws_an_exception_when_the_filesystem_does_not_exist(): void
    {
        $this->expectException(\LogicException::class);
        $this->sut->getFilesystem('baz');
    }
}
