<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Writer;

use Akeneo\Tool\Component\Connector\Writer\DummyItemWriter;
use PHPUnit\Framework\TestCase;

class DummyItemWriterTest extends TestCase
{
    private DummyItemWriter $sut;

    protected function setUp(): void
    {
        $this->sut = new DummyItemWriter();
    }

    public function test_it_does_nothing_when_writes_items(): void
    {
        $this->assertNull($this->sut->write(['foo', 'barr']));
    }
}
