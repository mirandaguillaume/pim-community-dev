<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Filter;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Filter\QualityScoreFilter;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\GetScoresPropertyStrategy;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QualityScoreFilterTest extends TestCase
{
    private SearchQueryBuilder|MockObject $queryBuilder;
    private GetScoresPropertyStrategy|MockObject $getScoresPropertyStrategy;
    private QualityScoreFilter $sut;

    protected function setUp(): void
    {
        $this->queryBuilder = $this->createMock(SearchQueryBuilder::class);
        $this->getScoresPropertyStrategy = $this->createMock(GetScoresPropertyStrategy::class);
        $this->sut = new QualityScoreFilter($this->getScoresPropertyStrategy);
        $this->getScoresPropertyStrategy->method('__invoke')->willReturn('scores');
        $this->sut->setQueryBuilder($this->queryBuilder);
    }

    public function test_it_adds_filter_on_quality_score_with_letter_values(): void
    {
        $this->queryBuilder->expects($this->once())->method('addFilter')->with([
                        'terms' => [
                            'data_quality_insights.scores.ecommerce.en_US' => [1, 2],
                        ],
                    ]);
        $this->sut->addFieldFilter('quality_score', Operators::IN_LIST, ['A', 'B'], 'en_US', 'ecommerce', []);
    }

    public function test_it_adds_filter_on_quality_score_with_integer_values(): void
    {
        $this->queryBuilder->expects($this->once())->method('addFilter')->with([
                        'terms' => [
                            'data_quality_insights.scores.ecommerce.en_US' => [1, 3],
                        ],
                    ]);
        $this->sut->addFieldFilter('data_quality_insights_score', Operators::IN_LIST, [1, 3], 'en_US', 'ecommerce', []);
    }

    public function test_it_throws_an_exception_if_the_values_are_not_an_array(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->addFieldFilter(
            'data_quality_insights_score',
            Operators::IN_LIST,
            2,
            'en_US',
            'ecommerce',
            [],
        );
    }

    public function test_it_throws_an_exception_if_there_is_no_channel(): void
    {
        $this->expectException(InvalidPropertyException::class);
        $this->sut->addFieldFilter(
            'quality_score',
            Operators::IN_LIST,
            ['A', 'B'],
            null,
            'ecommerce',
            [],
        );
    }

    public function test_it_throws_an_exception_if_there_is_no_locale(): void
    {
        $this->expectException(InvalidPropertyException::class);
        $this->sut->addFieldFilter(
            'quality_score',
            Operators::IN_LIST,
            ['A', 'B'],
            'en_US',
            null,
            [],
        );
    }

    public function test_it_throws_an_exception_if_a_value_is_invalid(): void
    {
        $this->expectException(InvalidPropertyException::class);
        $this->sut->addFieldFilter(
            'quality_score',
            Operators::IN_LIST,
            ['A', 'Z', 'B'],
            'en_US',
            'ecommerce',
            [],
        );
    }
}
