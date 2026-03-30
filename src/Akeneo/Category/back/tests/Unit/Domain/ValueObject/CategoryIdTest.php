<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject;

use Akeneo\Category\Domain\ValueObject\CategoryId;
use PHPUnit\Framework\TestCase;

class CategoryIdTest extends TestCase
{
    public function testItCreatesAValidCategoryId(): void
    {
        $id = new CategoryId(42);
        $this->assertSame(42, $id->getValue());
    }

    public function testItRejectsZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CategoryId(0);
    }

    public function testItRejectsNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CategoryId(-1);
    }

    public function testItAcceptsOne(): void
    {
        $id = new CategoryId(1);
        $this->assertSame(1, $id->getValue());
    }
}
