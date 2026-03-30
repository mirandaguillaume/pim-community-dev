<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistributionCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardRatesProjection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;
use PHPUnit\Framework\TestCase;

class DashboardRatesProjectionTest extends TestCase
{
    private DashboardRatesProjection $sut;

    protected function setUp(): void
    {
    }

    public function test_it_returns_the_ranks_distributions_for_a_common_day(): void
    {
        $consolidationDate = new ConsolidationDate(new \DateTimeImmutable('2020-01-20'));
        $ranksDistributionCollection = $this->buildRandomRanksDistributionCollection();
        $this->sut = new DashboardRatesProjection(
            DashboardProjectionType::catalog(),
            DashboardProjectionCode::catalog(),
            $consolidationDate,
            $ranksDistributionCollection
        );
        $this->assertEquals([
                    TimePeriod::DAILY => [
                        $consolidationDate->format() => $ranksDistributionCollection->toArray(),
                    ],
                ], $this->sut->getRanksDistributionsPerTimePeriod());
    }

    public function test_it_returns_the_ranks_distributions_for_a_last_day_of_a_week(): void
    {
        $consolidationDate = new ConsolidationDate(new \DateTimeImmutable('2020-01-19'));
        $ranksDistributionCollection = $this->buildRandomRanksDistributionCollection();
        $this->sut = new DashboardRatesProjection(
            DashboardProjectionType::catalog(),
            DashboardProjectionCode::catalog(),
            $consolidationDate,
            $ranksDistributionCollection
        );
        $this->assertEquals([
                    TimePeriod::DAILY => [
                        $consolidationDate->format() => $ranksDistributionCollection->toArray(),
                    ],
                    TimePeriod::WEEKLY => [
                        $consolidationDate->format() => $ranksDistributionCollection->toArray(),
                    ],
                ], $this->sut->getRanksDistributionsPerTimePeriod());
    }

    public function test_it_returns_the_ranks_distributions_for_a_last_day_of_a_month(): void
    {
        $consolidationDate = new ConsolidationDate(new \DateTimeImmutable('2020-01-31'));
        $ranksDistributionCollection = $this->buildRandomRanksDistributionCollection();
        $this->sut = new DashboardRatesProjection(
            DashboardProjectionType::catalog(),
            DashboardProjectionCode::catalog(),
            $consolidationDate,
            $ranksDistributionCollection
        );
        $this->assertEquals([
                    TimePeriod::DAILY => [
                        $consolidationDate->format() => $ranksDistributionCollection->toArray(),
                    ],
                    TimePeriod::MONTHLY => [
                        $consolidationDate->format() => $ranksDistributionCollection->toArray(),
                    ],
                ], $this->sut->getRanksDistributionsPerTimePeriod());
    }

    public function test_it_returns_the_ranks_distributions_for_a_last_day_of_a_year(): void
    {
        $consolidationDate = new ConsolidationDate(new \DateTimeImmutable('2019-12-31'));
        $ranksDistributionCollection = $this->buildRandomRanksDistributionCollection();
        $this->sut = new DashboardRatesProjection(
            DashboardProjectionType::catalog(),
            DashboardProjectionCode::catalog(),
            $consolidationDate,
            $ranksDistributionCollection
        );
        $this->assertEquals([
                    TimePeriod::DAILY => [
                        $consolidationDate->format() => $ranksDistributionCollection->toArray(),
                    ],
                    TimePeriod::MONTHLY => [
                        $consolidationDate->format() => $ranksDistributionCollection->toArray(),
                    ],
                    TimePeriod::YEARLY => [
                        $consolidationDate->format() => $ranksDistributionCollection->toArray(),
                    ],
                ], $this->sut->getRanksDistributionsPerTimePeriod());
    }

    private function buildRandomRanksDistributionCollection(): RanksDistributionCollection
    {
        return new RanksDistributionCollection([
            "ecommerce" => [
                "en_US" => [
                    "rank_1" => random_int(1, 100),
                    "rank_2" => random_int(1, 100),
                    "rank_3" => random_int(1, 100),
                    "rank_4" => random_int(1, 100),
                    "rank_5" => random_int(1, 100),
                ],
            ],
        ]);
    }
}
