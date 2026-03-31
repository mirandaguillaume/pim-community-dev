<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Reader;

use Akeneo\Tool\Component\Connector\Reader\DummyItemReader;
use PHPUnit\Framework\TestCase;

class DummyItemReaderTest extends TestCase
{
    private DummyItemReader $sut;

    protected function setUp(): void
    {
        $this->sut = new DummyItemReader();
    }

    public function test_it_does_nothing_when_read_items(): void
    {
        $this->assertNull($this->sut->read());
    }
}
