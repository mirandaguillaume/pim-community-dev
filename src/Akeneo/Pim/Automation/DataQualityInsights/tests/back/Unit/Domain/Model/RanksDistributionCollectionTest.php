<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistributionCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use PHPUnit\Framework\TestCase;

class RanksDistributionCollectionTest extends TestCase
{
    private RanksDistributionCollection $sut;

    protected function setUp(): void
    {
    }

    public function test_it_throws_an_exception_if_the_ranks_per_locale_are_malformed(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RanksDistributionCollection([
                    "mobile" => null,
                    "ecommerce" => [
                        "en_US" => [
                            "rank_1" => 33,
                        ],
                    ],
                ]);
    }

    public function test_it_throws_an_exception_if_the_ranks_are_malformed(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RanksDistributionCollection([
                    "mobile" => [
                        "en_US" => null,
                    ],
                    "ecommerce" => [
                        "en_US" => [
                            "rank_1" => 33,
                        ],
                    ],
                ]);
    }

    public function test_it_returns_the_average_ranks(): void
    {
        $this->sut = new RanksDistributionCollection([
                    "mobile" => [
                        "en_US" => [
                            'rank_1' => 10,
                            'rank_2' => 42,
                            'rank_3' => 5,
                        ],
                        "fr_FR" => [
                            "rank_3" => 33,
                        ],
                    ],
                ]);
        $this->assertEquals([
                    "mobile" => [
                        "en_US" => Rank::fromString('rank_2'),
                        "fr_FR" => Rank::fromString('rank_3'),
                    ],
                ], $this->sut->getAverageRanks());
    }
}
