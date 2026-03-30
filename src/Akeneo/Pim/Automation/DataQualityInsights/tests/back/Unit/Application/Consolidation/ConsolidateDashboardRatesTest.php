<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\Consolidation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateDashboardRates;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\RanksDistributionCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\DashboardRatesProjection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetRanksDistributionFromProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllCategoryCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllFamilyCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\DashboardScoresProjectionRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CategoryCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DashboardProjectionType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConsolidateDashboardRatesTest extends TestCase
{
    private GetRanksDistributionFromProductScoresQueryInterface|MockObject $getRanksDistributionFromProductAxisRatesQuery;
    private GetAllCategoryCodesQueryInterface|MockObject $getAllCategoryCodesQuery;
    private GetAllFamilyCodesQueryInterface|MockObject $getAllFamilyCodesQuery;
    private DashboardScoresProjectionRepositoryInterface|MockObject $dashboardRatesProjectionRepository;
    private ConsolidateDashboardRates $sut;

    protected function setUp(): void
    {
        $this->getRanksDistributionFromProductAxisRatesQuery = $this->createMock(GetRanksDistributionFromProductScoresQueryInterface::class);
        $this->getAllCategoryCodesQuery = $this->createMock(GetAllCategoryCodesQueryInterface::class);
        $this->getAllFamilyCodesQuery = $this->createMock(GetAllFamilyCodesQueryInterface::class);
        $this->dashboardRatesProjectionRepository = $this->createMock(DashboardScoresProjectionRepositoryInterface::class);
        $this->sut = new ConsolidateDashboardRates($this->getRanksDistributionFromProductAxisRatesQuery, $this->getAllCategoryCodesQuery, $this->getAllFamilyCodesQuery, $this->dashboardRatesProjectionRepository);
    }

    public function test_it_consolidates_the_dashboard_rates(): void
    {
        $dateTime = new \DateTimeImmutable('2020-01-19');
        $consolidationDate = new ConsolidationDate($dateTime);
        $catalogRanks = $this->buildRandomRanksDistributionCollection();
        $this->getRanksDistributionFromProductAxisRatesQuery->method('forWholeCatalog')->with($dateTime)->willReturn($catalogRanks);
        $catalogRatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::catalog(),
            DashboardProjectionCode::catalog(),
            $consolidationDate,
            $catalogRanks
        );
        $this->dashboardRatesProjectionRepository->expects($this->once())->method('save')->with($catalogRatesProjection);
        $familyMugsCode = new FamilyCode('mugs');
        $familyWebcamsCode = new FamilyCode('webcams');
        $familyMugsRanks = $this->buildRandomRanksDistributionCollection();
        $familyWebcamsRanks = $this->buildRandomRanksDistributionCollection();
        $this->getAllFamilyCodesQuery->method('execute')->willReturn([$familyMugsCode, $familyWebcamsCode]);
        $this->getRanksDistributionFromProductAxisRatesQuery->method('byFamily')->with($familyMugsCode, $dateTime)->willReturn($familyMugsRanks);
        $this->getRanksDistributionFromProductAxisRatesQuery->method('byFamily')->with($familyWebcamsCode, $dateTime)->willReturn($familyWebcamsRanks);
        $familyMugsRatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::family(),
            DashboardProjectionCode::family($familyMugsCode),
            $consolidationDate,
            $familyMugsRanks
        );
        $familyWebcamsRatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::family(),
            DashboardProjectionCode::family($familyWebcamsCode),
            $consolidationDate,
            $familyWebcamsRanks
        );
        $this->dashboardRatesProjectionRepository->expects($this->once())->method('save')->with($familyMugsRatesProjection);
        $this->dashboardRatesProjectionRepository->expects($this->once())->method('save')->with($familyWebcamsRatesProjection);
        $category1Code = new CategoryCode('category_1');
        $category2Code = new CategoryCode('category_2');
        $category1Ranks = $this->buildRandomRanksDistributionCollection();
        $category2Ranks = $this->buildRandomRanksDistributionCollection();
        $this->getAllCategoryCodesQuery->method('execute')->willReturn([$category1Code, $category2Code]);
        $this->getRanksDistributionFromProductAxisRatesQuery->method('byCategory')->with($category1Code, $dateTime)->willReturn($category1Ranks);
        $this->getRanksDistributionFromProductAxisRatesQuery->method('byCategory')->with($category2Code, $dateTime)->willReturn($category2Ranks);
        $category1RatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::category(),
            DashboardProjectionCode::category($category1Code),
            $consolidationDate,
            $category1Ranks
        );
        $category2RatesProjection = new DashboardRatesProjection(
            DashboardProjectionType::category(),
            DashboardProjectionCode::category($category2Code),
            $consolidationDate,
            $category2Ranks
        );
        $this->dashboardRatesProjectionRepository->expects($this->once())->method('save')->with($category1RatesProjection);
        $this->dashboardRatesProjectionRepository->expects($this->once())->method('save')->with($category2RatesProjection);
        $this->sut->consolidate($consolidationDate);
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
