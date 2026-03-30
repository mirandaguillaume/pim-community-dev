<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Writer\File;

use Akeneo\Tool\Component\Connector\Writer\File\WrittenFileInfo;
use PHPUnit\Framework\TestCase;

class WrittenFileInfoTest extends TestCase
{
    private WrittenFileInfo $sut;

    protected function setUp(): void
    {
    }

    public function test_it_cannot_be_instantiated_with_an_empty_key(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        WrittenFileInfo::fromFileStorage('', 'catalogStorage', 'files/media.png');
    }

    public function test_it_cannot_be_instantiated_with_an_empty_storage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        WrittenFileInfo::fromFileStorage('a/b/c/media.png', '', 'files/media.png');
    }

    public function test_it_cannot_be_instantiated_with_an_empty_filepath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        WrittenFileInfo::fromFileStorage('a/b/c/media.png', 'catalogStorage', '');
    }

    public function test_it_can_describe_a_remote_storage_file(): void
    {
        $this->sut = WrittenFileInfo::fromFileStorage('a/b/c/media.png', 'catalogStorage', 'files/media.png');
        $this->assertTrue(is_a(WrittenFileInfo::class, WrittenFileInfo::class, true));
        $this->assertSame(false, $this->sut->isLocalFile());
    }

    public function test_it_can_describe_a_local_file(): void
    {
        $this->sut = WrittenFileInfo::fromLocalFile('a/b/c/media.png', 'files/media.png');
        $this->assertTrue(is_a(WrittenFileInfo::class, WrittenFileInfo::class, true));
        $this->assertSame(true, $this->sut->isLocalFile());
    }
}
