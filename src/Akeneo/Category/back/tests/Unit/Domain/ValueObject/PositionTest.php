<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject;

use Akeneo\Category\Domain\ValueObject\Position;
use PHPUnit\Framework\TestCase;

class PositionTest extends TestCase
{
    public function test_it_creates_a_valid_position(): void
    {
        $position = new Position(1, 2, 0);

        $this->assertSame(1, $position->left);
        $this->assertSame(2, $position->right);
        $this->assertSame(0, $position->level);
    }

    public function test_it_rejects_zero_left(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Position(0, 2, 0);
    }

    public function test_it_rejects_negative_left(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Position(-1, 2, 0);
    }

    public function test_it_rejects_zero_right(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Position(1, 0, 0);
    }

    public function test_it_rejects_negative_right(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Position(1, -1, 0);
    }

    public function test_it_accepts_zero_level(): void
    {
        $position = new Position(1, 2, 0);
        $this->assertSame(0, $position->level);
    }

    public function test_it_accepts_positive_level(): void
    {
        $position = new Position(1, 2, 3);
        $this->assertSame(3, $position->level);
    }
}
