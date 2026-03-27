<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject\Attribute;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use PHPUnit\Framework\TestCase;

class AttributeOrderTest extends TestCase
{
    public function test_it_creates_a_valid_order(): void
    {
        $order = AttributeOrder::fromInteger(5);
        $this->assertSame(5, $order->intValue());
    }

    public function test_it_accepts_zero(): void
    {
        $order = AttributeOrder::fromInteger(0);
        $this->assertSame(0, $order->intValue());
    }

    public function test_it_rejects_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        AttributeOrder::fromInteger(-1);
    }

    public function test_equals_returns_true_for_same_value(): void
    {
        $a = AttributeOrder::fromInteger(3);
        $b = AttributeOrder::fromInteger(3);
        $this->assertTrue($a->equals($b));
    }

    public function test_equals_returns_false_for_different_value(): void
    {
        $a = AttributeOrder::fromInteger(3);
        $b = AttributeOrder::fromInteger(4);
        $this->assertFalse($a->equals($b));
    }
}
