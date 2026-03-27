<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject\Attribute;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use PHPUnit\Framework\TestCase;

class AttributeOrderTest extends TestCase
{
    public function testItCreatesAValidOrder(): void
    {
        $order = AttributeOrder::fromInteger(5);
        $this->assertSame(5, $order->intValue());
    }

    public function testItAcceptsZero(): void
    {
        $order = AttributeOrder::fromInteger(0);
        $this->assertSame(0, $order->intValue());
    }

    public function testItRejectsNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AttributeOrder::fromInteger(-1);
    }

    public function testEqualsReturnsTrueForSameValue(): void
    {
        $a = AttributeOrder::fromInteger(3);
        $b = AttributeOrder::fromInteger(3);
        $this->assertTrue($a->equals($b));
    }

    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        $a = AttributeOrder::fromInteger(3);
        $b = AttributeOrder::fromInteger(4);
        $this->assertFalse($a->equals($b));
    }
}
