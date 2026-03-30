<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject;

use Akeneo\Category\Domain\ValueObject\Position;
use PHPUnit\Framework\TestCase;

class PositionTest extends TestCase
{
    public function testItCreatesAValidPosition(): void
    {
        $position = new Position(1, 2, 0);

        $this->assertSame(1, $position->left);
        $this->assertSame(2, $position->right);
        $this->assertSame(0, $position->level);
    }

    public function testItRejectsZeroLeft(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Position(0, 2, 0);
    }

    public function testItRejectsNegativeLeft(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Position(-1, 2, 0);
    }

    public function testItRejectsZeroRight(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Position(1, 0, 0);
    }

    public function testItRejectsNegativeRight(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Position(1, -1, 0);
    }

    public function testItAcceptsZeroLevel(): void
    {
        $position = new Position(1, 2, 0);
        $this->assertSame(0, $position->level);
    }

    public function testItAcceptsPositiveLevel(): void
    {
        $position = new Position(1, 2, 3);
        $this->assertSame(3, $position->level);
    }
}
