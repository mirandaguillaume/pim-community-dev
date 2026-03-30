<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Domain\ValueObject;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PHPUnit\Framework\TestCase;

class RankTest extends TestCase
{
    private Rank $sut;

    protected function setUp(): void
    {
    }

    public function test_it_can_be_constructed_from_a_rank_code(): void
    {
        $this->sut = Rank::fromString('rank_1');
        $this->assertSame(1, $this->sut->toInt());
    }

    public function test_it_throws_an_exception_if_the_code_is_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Rank::fromString('foo_1');
    }

    public function test_it_can_be_constructed_from_a_rank_value(): void
    {
        $this->sut = Rank::fromInt(2);
        $this->assertSame(2, $this->sut->toInt());
    }

    public function test_it_throws_an_exception_if_the_value_is_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Rank::fromInt(42);
    }

    public function test_it_can_be_constructed_from_a_rank_letter(): void
    {
        $this->sut = Rank::fromLetter('C');
        $this->assertSame(3, $this->sut->toInt());
    }

    public function test_it_throws_an_exception_if_the_letter_is_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Rank::fromLetter('Z');
    }

    public function test_it_can_be_constructed_from_a_rate(): void
    {
        $this->sut = Rank::fromRate(new Rate(61));
        $this->assertSame(4, $this->sut->toInt());
    }
}
