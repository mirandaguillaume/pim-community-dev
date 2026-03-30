<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\ArrayConverter;

use Akeneo\Tool\Component\Connector\ArrayConverter\FieldSplitter;
use PHPUnit\Framework\TestCase;

class FieldSplitterTest extends TestCase
{
    private FieldSplitter $sut;

    protected function setUp(): void
    {
        $this->sut = new FieldSplitter();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FieldSplitter::class, $this->sut);
    }

    public function test_it_split_field_name(): void
    {
        $this->assertSame(['description', 'en_US', 'mobile'], $this->sut->splitFieldName('description-en_US-mobile'));
        $this->assertSame(['description', 'en_US'], $this->sut->splitFieldName('description-en_US'));
        $this->assertSame(['description'], $this->sut->splitFieldName('description'));
        $this->assertSame(['description', '', 'mobile'], $this->sut->splitFieldName('description--mobile'));
        $this->assertSame(['description', '', ''], $this->sut->splitFieldName('description--'));
        $this->assertSame([], $this->sut->splitFieldName(''));
    }
}
