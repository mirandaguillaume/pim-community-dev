<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Buffer;

use Akeneo\Tool\Component\Buffer\BufferInterface;
use Akeneo\Tool\Component\Buffer\Exception\UnsupportedItemTypeException;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Buffer\JSONFileBuffer;

class JSONFileBufferTest extends TestCase
{
    private JSONFileBuffer $sut;

    protected function setUp(): void
    {
        $this->sut = new JSONFileBuffer();
    }

    public function test_it_is_a_buffer(): void
    {
        $this->assertInstanceOf(BufferInterface::class, $this->sut);
    }

    public function test_it_writes_and_reads_several_items_fifo_style(): void
    {
        $items = ['item_1', 'item_2', 'item_3'];
        foreach ($items as $item) {
            $this->sut->write($item);
        }
        $readItems = [];
        foreach ($this->sut as $item) {
            $readItems[] = $item;
        }
        if ($items !== $readItems) {
            throw new FailedPredictionException(sprintf(
                'Expected items "%s", got "%s"',
                implode(', ', $items),
                implode(', ', $readItems)
            ));
        }
    }

    public function test_it_supports_only_scalar_and_array_items(): void
    {
        $this->sut->write('scalar');
        $this->sut->write(['scalar']);
        $this->expectException(UnsupportedItemTypeException::class);
        $this->sut->write(new \stdClass());
    }

    public function test_it_switches_correctly_between_write_and_read_mode(): void
    {
        $this->sut->write('item_1');
        $this->sut->write('item_2');
        foreach ($this->sut as $item) {
            // do stuff with read items
        }
        $this->sut->write('item_3');
        $readItems = [];
        foreach ($this->sut as $item) {
            $readItems[] = $item;
        }
        $items = ['item_1', 'item_2', 'item_3'];
        if ($items !== $readItems) {
            throw new FailedPredictionException(sprintf(
                'Expected items "%s", got "%s"',
                implode(', ', $items),
                implode(', ', $readItems)
            ));
        }
    }
}
