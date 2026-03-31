<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchBundle\Item\Support;

use Akeneo\Tool\Bundle\BatchBundle\Item\Support\UcfirstProcessor;
use PHPUnit\Framework\TestCase;

class UcfirstProcessorTest extends TestCase
{
    private UcfirstProcessor $sut;

    protected function setUp(): void
    {
        $this->sut = new UcfirstProcessor();
    }

    public function test_it_processes(): void
    {
        $this->assertSame('Item1', $this->sut->process('item1'));
    }
}
