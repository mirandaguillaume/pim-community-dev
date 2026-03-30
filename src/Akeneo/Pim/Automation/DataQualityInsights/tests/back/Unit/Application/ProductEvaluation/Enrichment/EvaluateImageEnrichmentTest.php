<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\CalculateProductCompletenessInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateImageEnrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Utils\CatalogProvider;
use Akeneo\Pim\Automation\DataQualityInsights\tests\back\Specification\Utils\EvaluationProvider;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class EvaluateImageEnrichmentTest extends TestCase
{
    private CalculateProductCompletenessInterface|MockObject $completenessCalculator;
    private GetLocalesByChannelQueryInterface|MockObject $localesByChannelQuery;
    private EvaluateImageEnrichment $sut;

    protected function setUp(): void
    {
        $this->completenessCalculator = $this->createMock(CalculateProductCompletenessInterface::class);
        $this->localesByChannelQuery = $this->createMock(GetLocalesByChannelQueryInterface::class);
        $this->sut = new EvaluateImageEnrichment($this->completenessCalculator, $this->localesByChannelQuery);
    }

    public function test_it_evaluates_the_image_enrichment_for_a_product_with_image(): void
    {
        $productUuid = ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed'));
        $criterionEvaluation = EvaluationProvider::aWritableCriterionEvaluation(
            EvaluateImageEnrichment::CRITERION_CODE,
            CriterionEvaluationStatus::DONE,
            Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed')
        );
        $imageAttribute = CatalogProvider::anAttribute('an_image_attribute', AttributeTypes::IMAGE);
        $secondImageAttribute = CatalogProvider::anAttribute('a_second_image_attribute', AttributeTypes::IMAGE);
        $textAttribute = CatalogProvider::anAttribute('a_text_attribute');
        $productValues = CatalogProvider::aListOfProductValues([
                    ['attribute' => $imageAttribute, 'values' => ['a_channel' => ['en_US' => '/an_en_image.jpg', 'fr_FR' => '/an_fr_image.jpg', 'de_DE' => '']]],
                    ['attribute' => $secondImageAttribute, 'values' => ['a_channel' => ['en_US' => '/an_en_image.jpg', 'fr_FR' => '', 'de_DE' => '']]],
                    ['attribute' => $textAttribute, 'values' => ['a_channel' => ['en_US' => '', 'fr_FR' => '', 'de_DE' => '']]],
                ]);
        $channelsWithLocales = CatalogProvider::aListOfChannelsWithLocales([
                    'a_channel' => ['en_US', 'fr_FR', 'de_DE'],
                ]);
        $completenessResult = EvaluationProvider::aWritableCompletenessCalculationResult([
                    'a_channel' => [
                        'en_US' => ['rate' => 100, 'attributes' => []],
                        'fr_FR' => ['rate' => 50, 'attributes' => ['a_second_image']],
                        'de_DE' => ['rate' => 0, 'attributes' => ['an_image', 'a_second_image']],
                    ],
                ]);
        $expectedResult = EvaluationProvider::aWritableCriterionEvaluationResult([
                    'a_channel' => [
                        'en_US' => [
                            'rate' => 100,
                            'attributes' => [],
                            'status' => 'done',
                        ],
                        'fr_FR' => [
                            'rate' => 100,
                            'attributes' => ['a_second_image' => 0],
                            'status' => 'done',
                        ],
                        'de_DE' => [
                            'rate' => 0,
                            'attributes' => ['an_image' => 0, 'a_second_image' => 0],
                            'status' => 'done',
                        ],
                    ],
                ]);
        $this->localesByChannelQuery->method('getChannelLocaleCollection')->willReturn($channelsWithLocales);
        $this->completenessCalculator->method('calculate')->with($productUuid)->willReturn($completenessResult);
        $this->assertEquals($expectedResult, $this->sut->evaluate($criterionEvaluation, $productValues));
    }

    public function test_it_evaluates_the_image_enrichment_for_a_product_without_image(): void
    {
        $productUuid = ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed'));
        $criterionEvaluation = EvaluationProvider::aWritableCriterionEvaluation(
            EvaluateImageEnrichment::CRITERION_CODE,
            CriterionEvaluationStatus::DONE,
            Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed')
        );
        $textAttribute = CatalogProvider::anAttribute('a_text_attribute');
        $productValues = CatalogProvider::aListOfProductValues([
                    ['attribute' => $textAttribute, 'values' => ['a_channel' => ['en_US' => '', 'fr_FR' => '']]],
                ]);
        $channelsWithLocales = CatalogProvider::aListOfChannelsWithLocales([
                    'a_channel' => ['en_US', 'fr_FR'],
                ]);
        $completenessResult = EvaluationProvider::aWritableCompletenessCalculationResult([
                    'a_channel' => [
                        'en_US' => ['rate' => null],
                        'fr_FR' => ['rate' => 0, 'attributes' => []],
                    ],
                ]);
        $expectedResult = EvaluationProvider::aWritableCriterionEvaluationResult([
                    'a_channel' => [
                        'en_US' => [
                            'status' => 'not_applicable',
                        ],
                        'fr_FR' => [
                            'rate' => 0,
                            'attributes' => [],
                            'status' => 'done',
                        ],
                    ],
                ]);
        $this->localesByChannelQuery->method('getChannelLocaleCollection')->willReturn($channelsWithLocales);
        $this->completenessCalculator->method('calculate')->with($productUuid)->willReturn($completenessResult);
        $this->assertEquals($expectedResult, $this->sut->evaluate($criterionEvaluation, $productValues));
    }
}
