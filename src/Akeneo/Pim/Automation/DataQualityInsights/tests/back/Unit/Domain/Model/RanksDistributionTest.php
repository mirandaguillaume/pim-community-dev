<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistribution;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use PHPUnit\Framework\TestCase;

class RanksDistributionTest extends TestCase
{
    private RanksDistribution $sut;

    protected function setUp(): void
    {
    }

    public function test_it_throws_an_exception_if_it_contains_an_invalid_rank(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RanksDistribution([
                    'rank_1' => 1,
                    'rank_2' => 1,
                    'rank_3' => 1,
                    'rank_4' => 1,
                    'rank_5' => 1,
                    'rank_6' => 1,
                ]);
    }

    public function test_it_returns_the_percentage_per_rank(): void
    {
        $this->sut = new RanksDistribution([
                    'rank_1' => 137,
                    'rank_2' => 49,
                    'rank_3' => 151,
                    'rank_4' => 0,
                    'rank_5' => 233,
                ]);
        $this->assertSame([
                    'rank_1' => 24.04,
                    'rank_2' => 8.6,
                    'rank_3' => 26.49,
                    'rank_4' => 0.0,
                    'rank_5' => 40.88,
                ], $this->sut->getPercentages());
    }

    public function test_it_returns_the_average_rank(): void
    {
        $this->sut = new RanksDistribution([
                    'rank_1' => 137,
                    'rank_2' => 49,
                    'rank_3' => 151,
                    'rank_4' => 0,
                    'rank_5' => 133,
                ]);
        $this->assertEquals(Rank::fromInt(3), $this->sut->getAverageRank());
    }

    public function test_it_returns_null_as_average_rank_if_all_the_ranks_are_empty(): void
    {
        $this->sut = new RanksDistribution([
                    'rank_1' => 0,
                    'rank_2' => 0,
                    'rank_3' => 0,
                    'rank_4' => 0,
                    'rank_5' => 0,
                ]);
        $this->assertNull($this->sut->getAverageRank());
    }
}
