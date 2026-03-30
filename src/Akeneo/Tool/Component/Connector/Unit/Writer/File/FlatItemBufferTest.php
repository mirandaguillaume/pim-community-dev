<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Writer\File;

use Akeneo\Tool\Component\Buffer\BufferInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FlatItemBuffer;
use PHPUnit\Framework\TestCase;

class FlatItemBufferTest extends TestCase
{
    private FlatItemBuffer $sut;

    protected function setUp(): void
    {
        $this->sut = new FlatItemBuffer();
    }

    public function test_it_is_a_buffer(): void
    {
        $this->assertInstanceOf(BufferInterface::class, $this->sut);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FlatItemBuffer::class, $this->sut);
    }

    public function test_it_writes_item_with_headers(): void
    {
        $this->sut->write([
                    [
                        'id' => 123,
                        'family' => 12,
                    ],
                    [
                        'id' => 165,
                        'family' => 45,
                    ],
                ], ['withHeader' => true]);
        $this->assertSame(['id', 'family'], $this->sut->getHeaders());
    }

    public function test_it_counts_written_items_to_the_buffer(): void
    {
        $this->sut->write([
                    [
                        'id' => 123,
                        'family' => 12,
                    ],
                    [
                        'id' => 165,
                        'family' => 45,
                    ],
                ]);
        $this->assertSame(2, $this->sut->count());
        $this->sut->write([
                    [
                        'id' => 456,
                        'family' => 12,
                    ],
                ]);
        $this->assertSame(3, $this->sut->count());
    }
}
