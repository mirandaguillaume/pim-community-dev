<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid\QualityScoreFilter;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;

class QualityScoreFilterTest extends TestCase
{
    private FormFactoryInterface|MockObject $formFactory;
    private FilterUtility|MockObject $filterUtility;
    private QualityScoreFilter $sut;

    protected function setUp(): void
    {
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->filterUtility = $this->createMock(FilterUtility::class);
        $this->sut = new QualityScoreFilter($this->formFactory, $this->filterUtility);
    }

    public function test_it_applies_the_quality_score_filter(): void
    {
        $filterDatasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->filterUtility->expects($this->once())->method('applyFilter')->with($filterDatasource, 'data_quality_insights_score', 'IN', [1, 3]);
        $this->sut->apply($filterDatasource, ['value' => [1, 3]]);
    }

    public function test_it_does_not_apply_quality_score_filter_when_the_filter_values_are_empty(): void
    {
        $filterDatasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->filterUtility->expects($this->never())->method('applyFilter');
        $this->sut->apply($filterDatasource, ['value' => []]);
    }

    public function test_it_does_not_apply_quality_score_filter_when_the_filter_values_are_not_an_array(): void
    {
        $filterDatasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->filterUtility->expects($this->never())->method('applyFilter');
        $this->sut->apply($filterDatasource, ['value' => 1]);
    }
}
