<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchBundle\Item\Support;

use Akeneo\Tool\Bundle\BatchBundle\Item\Support\ArrayReader;
use PHPUnit\Framework\TestCase;

class ArrayReaderTest extends TestCase
{
    private ArrayReader $sut;

    protected function setUp(): void
    {
        $this->sut = new ArrayReader();
    }

    public function test_it_reads(): void
    {
        $this->sut->setItems(['item1', 'item2', 'item3']);
        $this->assertSame('item1', $this->sut->read());
        $this->assertSame('item2', $this->sut->read());
        $this->assertSame('item3', $this->sut->read());
        $this->assertNull($this->sut->read());
    }
}
