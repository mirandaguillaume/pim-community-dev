<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Filter;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Filter\QualityScoreMultiLocalesFilter;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\GetScoresPropertyStrategy;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QualityScoreMultiLocalesFilterTest extends TestCase
{
    private SearchQueryBuilder|MockObject $queryBuilder;
    private GetScoresPropertyStrategy|MockObject $getScoresPropertyStrategy;
    private QualityScoreMultiLocalesFilter $sut;

    protected function setUp(): void
    {
        $this->queryBuilder = $this->createMock(SearchQueryBuilder::class);
        $this->getScoresPropertyStrategy = $this->createMock(GetScoresPropertyStrategy::class);
        $this->sut = new QualityScoreMultiLocalesFilter($this->getScoresPropertyStrategy);
        $this->getScoresPropertyStrategy->method('__invoke')->willReturn('scores');
        $this->sut->setQueryBuilder($this->queryBuilder);
    }

    public function test_it_adds_filter_on_quality_score_for_at_least_one_locale(): void
    {
        $this->queryBuilder->expects($this->once())->method('addFilter')->with([
                        'bool' => [
                            'should' => [
                                [
                                    'terms' => [
                                        'data_quality_insights.scores.ecommerce.en_US' => [1, 2],
                                    ],
                                ],
                                [
                                    'terms' => [
                                        'data_quality_insights.scores.ecommerce.fr_FR' => [1, 2],
                                    ],
                                ],
                            ],
                        ],

                    ]);
        $this->sut->addFieldFilter(
            QualityScoreMultiLocalesFilter::FIELD,
            QualityScoreMultiLocalesFilter::OPERATOR_IN_AT_LEAST_ONE_LOCALE,
            [1, 2],
            null,
            'ecommerce',
            [
                        'locales' => ['en_US', 'fr_FR'],
                    ]
        );
    }

    public function test_it_adds_filter_on_quality_score_for_all_locales(): void
    {
        $this->queryBuilder->expects($this->once())->method('addFilter')->with([
                        'bool' => [
                            'must' => [
                                [
                                    'terms' => [
                                        'data_quality_insights.scores.ecommerce.en_US' => [1, 2],
                                    ],
                                ],
                                [
                                    'terms' => [
                                        'data_quality_insights.scores.ecommerce.fr_FR' => [1, 2],
                                    ],
                                ],
                            ],
                        ],

                    ]);
        $this->sut->addFieldFilter(
            QualityScoreMultiLocalesFilter::FIELD,
            QualityScoreMultiLocalesFilter::OPERATOR_IN_ALL_LOCALES,
            [1, 2],
            null,
            'ecommerce',
            ['locales' => ['en_US', 'fr_FR']]
        );
    }

    public function test_it_throws_an_exception_if_the_values_are_not_an_array(): void
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->addFieldFilter(
            QualityScoreMultiLocalesFilter::FIELD,
            QualityScoreMultiLocalesFilter::OPERATOR_IN_ALL_LOCALES,
            2,
            null,
            'ecommerce',
            ['locales' => ['en_US', 'fr_FR']],
        );
    }

    public function test_it_throws_an_exception_if_there_is_no_channel(): void
    {
        $this->expectException(InvalidPropertyException::class);
        $this->sut->addFieldFilter(
            QualityScoreMultiLocalesFilter::FIELD,
            QualityScoreMultiLocalesFilter::OPERATOR_IN_ALL_LOCALES,
            [1, 3],
            null,
            null,
            ['locales' => ['en_US', 'fr_FR']],
        );
    }

    public function test_it_throws_an_exception_if_there_is_no_locale(): void
    {
        $this->expectException(InvalidPropertyException::class);
        $this->sut->addFieldFilter(
            QualityScoreMultiLocalesFilter::FIELD,
            QualityScoreMultiLocalesFilter::OPERATOR_IN_ALL_LOCALES,
            [1, 2],
            null,
            'ecommerce',
            ['locales' => []],
        );
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->addFieldFilter(
            QualityScoreMultiLocalesFilter::FIELD,
            QualityScoreMultiLocalesFilter::OPERATOR_IN_ALL_LOCALES,
            [1, 2],
            null,
            'ecommerce',
            ['locales' => 'en_US'],
        );
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->addFieldFilter(
            QualityScoreMultiLocalesFilter::FIELD,
            QualityScoreMultiLocalesFilter::OPERATOR_IN_ALL_LOCALES,
            [1, 2],
            null,
            'ecommerce',
            [],
        );
    }
}
