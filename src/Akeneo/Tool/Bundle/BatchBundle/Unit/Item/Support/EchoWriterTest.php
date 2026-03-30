<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchBundle\Item\Support;

use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\BatchBundle\Item\Support\EchoWriter;

class EchoWriterTest extends TestCase
{
    private EchoWriter $sut;

    protected function setUp(): void
    {
        $this->sut = new EchoWriter();
    }

    public function test_it_writes(): void
    {
        $this->assertNull($this->sut->write([]));
    }
}
