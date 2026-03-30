<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Reader\File;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Reader\File\ArrayReader;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ArrayReaderTest extends TestCase
{
    private FileReaderInterface|MockObject $reader;
    private ArrayConverterInterface|MockObject $converter;
    private ArrayReader $sut;

    protected function setUp(): void
    {
        $this->reader = $this->createMock(FileReaderInterface::class);
        $this->converter = $this->createMock(ArrayConverterInterface::class);
        $this->sut = new ArrayReader($this->reader, $this->converter);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ArrayReader::class, $this->sut);
    }

    public function test_it_is_a_file_reader(): void
    {
        $this->assertInstanceOf(FileReaderInterface::class, $this->sut);
    }

    public function test_it_returns_null_with_no_elements(): void
    {
        $this->reader->method('read')->willReturn(null);
        $this->assertNull($this->sut->read());
    }

    public function test_it_returns_element_one_by_one(): void
    {
        $this->reader->method('read')->willReturn(['sku' => 'foo', 'attr' => 'baz,bar']);
        $this->converter->method('convert')->with(['sku' => 'foo', 'attr' => 'baz,bar'])->willReturn([['code' => 'baz'], ['code' => 'bar']]);
        $this->assertEquals(['code' => 'baz'], $this->sut->read());
        $this->assertEquals(['code' => 'bar'], $this->sut->read());
    }
}
