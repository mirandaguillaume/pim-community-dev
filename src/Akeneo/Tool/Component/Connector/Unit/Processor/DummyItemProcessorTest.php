<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\Processor;

use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Connector\Processor\DummyItemProcessor;

class DummyItemProcessorTest extends TestCase
{
    private DummyItemProcessor $sut;

    protected function setUp(): void
    {
        $this->sut = new DummyItemProcessor();
    }

    public function test_it_does_nothing_when_process_items(): void
    {
        $this->assertNull($this->sut->process('foo'));
    }
}
