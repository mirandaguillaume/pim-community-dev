<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject;

use Akeneo\Category\Domain\ValueObject\CategoryId;
use PHPUnit\Framework\TestCase;

class CategoryIdTest extends TestCase
{
    public function test_it_creates_a_valid_category_id(): void
    {
        $id = new CategoryId(42);
        $this->assertSame(42, $id->getValue());
    }

    public function test_it_rejects_zero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CategoryId(0);
    }

    public function test_it_rejects_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CategoryId(-1);
    }

    public function test_it_accepts_one(): void
    {
        $id = new CategoryId(1);
        $this->assertSame(1, $id->getValue());
    }
}
