<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CompleteEvaluationWithImprovableAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\CalculateProductCompletenessInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionEvaluationResultStatusCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CompletenessCalculationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompleteEvaluationWithImprovableAttributesTest extends TestCase
{
    private GetLocalesByChannelQueryInterface|MockObject $localesByChannelQuery;
    private CalculateProductCompletenessInterface|MockObject $calculateRequiredAttributesCompleteness;
    private CalculateProductCompletenessInterface|MockObject $calculateNonRequiredAttributesCompleteness;
    private CompleteEvaluationWithImprovableAttributes $sut;

    protected function setUp(): void
    {
        $this->localesByChannelQuery = $this->createMock(GetLocalesByChannelQueryInterface::class);
        $this->calculateRequiredAttributesCompleteness = $this->createMock(CalculateProductCompletenessInterface::class);
        $this->calculateNonRequiredAttributesCompleteness = $this->createMock(CalculateProductCompletenessInterface::class);
        $this->sut = new CompleteEvaluationWithImprovableAttributes($this->localesByChannelQuery, $this->calculateRequiredAttributesCompleteness, $this->calculateNonRequiredAttributesCompleteness);
    }

    public function test_it_completes_a_product_evaluation_with_improvable_attributes(): void
    {
        $productUuid = ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed'));
        $criteriaEvaluations = $this->givenProductCriteriaEvaluationsWithCompleteness($productUuid);
        $channelCodeEcommerce = new ChannelCode('ecommerce');
        $channelCodeMobile = new ChannelCode('mobile');
        $localeCodeEn = new LocaleCode('en_US');
        $this->localesByChannelQuery->method('getChannelLocaleCollection')->willReturn(new ChannelLocaleCollection([
                    'ecommerce' => ['en_US'],
                    'mobile' => ['en_US'],
                ]));
        $requiredAttributesCompletenessResult = (new CompletenessCalculationResult())
                    ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(80))
                    ->addMissingAttributes($channelCodeEcommerce, $localeCodeEn, ['description', 'name'])
                    ->addRate($channelCodeMobile, $localeCodeEn, new Rate(100))
                    ->addMissingAttributes($channelCodeMobile, $localeCodeEn, []);
        $nonRequiredAttributesCompletenessResult = (new CompletenessCalculationResult())
                    ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(75))
                    ->addMissingAttributes($channelCodeEcommerce, $localeCodeEn, ['title', 'meta_title'])
                    ->addRate($channelCodeMobile, $localeCodeEn, new Rate(100))
                    ->addMissingAttributes($channelCodeMobile, $localeCodeEn, []);
        ;
        $this->calculateRequiredAttributesCompleteness->method('calculate')->with($productUuid)->willReturn($requiredAttributesCompletenessResult);
        $this->calculateNonRequiredAttributesCompleteness->method('calculate')->with($productUuid)->willReturn($nonRequiredAttributesCompletenessResult);
        $completedCriteriaEvaluations = ($this->sut)($criteriaEvaluations);
        $this->assertSame($criteriaEvaluations->count(), $completedCriteriaEvaluations->count());
        $completedRequiredCompletenessEvaluation = $completedCriteriaEvaluations->get(
            new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE)
        );
        $requiredCompletenessEvaluation = $criteriaEvaluations->get(
            new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE)
        );
        $this->assertEquals([
                    'total_number_of_attributes' => 12,
                    'attributes_with_rates' => [
                        'ecommerce' => [
                            'en_US' => ['description' => 0, 'name' => 0],
                        ],
                        'mobile' => ['en_US' => []],
                    ],
                ], $completedRequiredCompletenessEvaluation->getResult()->getData());
        $this->assertEquals([
                    'ecommerce' => [
                        'en_US' => 80,
                    ],
                    'mobile' => [
                        'en_US' => 100,
                    ],
                ], $completedRequiredCompletenessEvaluation->getResult()->getRates()->toArrayInt());
        $this->assertEquals($productUuid, $completedRequiredCompletenessEvaluation->getProductId());
        $this->assertEquals($requiredCompletenessEvaluation->getStatus(), $completedRequiredCompletenessEvaluation->getStatus());
        $this->assertEquals($requiredCompletenessEvaluation->getEvaluatedAt(), $completedRequiredCompletenessEvaluation->getEvaluatedAt());
        $this->assertEquals($requiredCompletenessEvaluation->getResult()->getRates(), $completedRequiredCompletenessEvaluation->getResult()->getRates());
        $this->assertEquals($requiredCompletenessEvaluation->getResult()->getStatus(), $completedRequiredCompletenessEvaluation->getResult()->getStatus());
        $completedNonRequiredCompletenessEvaluation = $completedCriteriaEvaluations->get(
            new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE)
        );
        $this->assertEquals([
                    'total_number_of_attributes' => 7,
                    'attributes_with_rates' => [
                        'ecommerce' => [
                            'en_US' => ['title' => 0, 'meta_title' => 0],
                        ],
                        'mobile' => ['en_US' => []],
                    ],
                ], $completedNonRequiredCompletenessEvaluation->getResult()->getData());
        $this->assertEquals([
                    'ecommerce' => [
                        'en_US' => 75,
                    ],
                    'mobile' => [
                        'en_US' => 100,
                    ],
                ], $completedNonRequiredCompletenessEvaluation->getResult()->getRates()->toArrayInt());
        $spellingCriterionCode = new CriterionCode('consistency_spelling');
        $this->assertEquals($criteriaEvaluations->get($spellingCriterionCode), $completedCriteriaEvaluations->get($spellingCriterionCode));
    }

    public function test_it_does_nothing_when_there_is_no_criterion_to_complete(): void
    {
        $productUuid = ProductUuid::fromString(('df470d52-7723-4890-85a0-e79be625e2ed'));
        $criteriaEvaluations = $this->givenProductCriteriaEvaluationsWithoutCompleteness($productUuid);
        $this->localesByChannelQuery->expects($this->never())->method('getChannelLocaleCollection');
        $this->calculateRequiredAttributesCompleteness->expects($this->never())->method('calculate')->with($this->anything());
        $this->calculateNonRequiredAttributesCompleteness->expects($this->never())->method('calculate')->with($this->anything());
        $this->assertSame($criteriaEvaluations, $this->sut->__invoke($criteriaEvaluations));
    }

    private function givenProductCriteriaEvaluationsWithCompleteness(ProductUuid $productId): CriterionEvaluationCollection
    {
        $channelCodeEcommerce = new ChannelCode('ecommerce');
        $channelCodeMobile = new ChannelCode('mobile');
        $localeCodeEn = new LocaleCode('en_US');
    
        $completenessOfRequiredAttributesRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(100))
            ->addRate($channelCodeMobile, $localeCodeEn, new Rate(70));
    
        $completenessOfRequiredAttributesStatus = (new CriterionEvaluationResultStatusCollection())
            ->add($channelCodeEcommerce, $localeCodeEn, CriterionEvaluationResultStatus::done())
            ->add($channelCodeMobile, $localeCodeEn, CriterionEvaluationResultStatus::done());
    
        $completenessOfNonRequiredAttributesRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(70));
    
        $completenessOfNonRequiredAttributesStatus = (new CriterionEvaluationResultStatusCollection())
            ->add($channelCodeEcommerce, $localeCodeEn, CriterionEvaluationResultStatus::done());
    
        $evaluateSpellingRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(88))
            ->addRate($channelCodeMobile, $localeCodeEn, new Rate(100))
        ;
        $evaluateSpellingStatus = (new CriterionEvaluationResultStatusCollection())
            ->add($channelCodeEcommerce, $localeCodeEn, CriterionEvaluationResultStatus::done())
            ->add($channelCodeMobile, $localeCodeEn, CriterionEvaluationResultStatus::done())
        ;
        $evaluateSpellingData = [
            "attributes_with_rates" => [
                "ecommerce" => [
                    "en_US" => ["description" => 86],
                ],
                "mobile" => [
                    "en_US" => [],
                ],
            ],
        ];
    
        return (new CriterionEvaluationCollection())
            ->add($this->generateCriterionEvaluation(
                $productId,
                EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE,
                CriterionEvaluationStatus::DONE,
                $completenessOfRequiredAttributesRates,
                $completenessOfRequiredAttributesStatus,
                ['total_number_of_attributes' => 12]
            ))
            ->add($this->generateCriterionEvaluation(
                $productId,
                EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE,
                CriterionEvaluationStatus::DONE,
                $completenessOfNonRequiredAttributesRates,
                $completenessOfNonRequiredAttributesStatus,
                ['total_number_of_attributes' => 7]
            ))
            ->add(
                $this->generateCriterionEvaluation(
                    $productId,
                    'consistency_spelling',
                    CriterionEvaluationStatus::DONE,
                    $evaluateSpellingRates,
                    $evaluateSpellingStatus,
                    $evaluateSpellingData
                )
            );
    }

    private function givenProductCriteriaEvaluationsWithoutCompleteness(ProductUuid $productId): CriterionEvaluationCollection
    {
        $channelCodeEcommerce = new ChannelCode('ecommerce');
        $channelCodeMobile = new ChannelCode('mobile');
        $localeCodeEn = new LocaleCode('en_US');
    
        $evaluateSpellingRates = (new ChannelLocaleRateCollection())
            ->addRate($channelCodeEcommerce, $localeCodeEn, new Rate(88))
            ->addRate($channelCodeMobile, $localeCodeEn, new Rate(100))
        ;
        $evaluateSpellingStatus = (new CriterionEvaluationResultStatusCollection())
            ->add($channelCodeEcommerce, $localeCodeEn, CriterionEvaluationResultStatus::done())
            ->add($channelCodeMobile, $localeCodeEn, CriterionEvaluationResultStatus::done())
        ;
        $evaluateSpellingData = [
            "attributes_with_rates" => [
                "ecommerce" => [
                    "en_US" => ["description" => 86],
                ],
                "mobile" => [
                    "en_US" => [],
                ],
            ],
        ];
    
        return (new CriterionEvaluationCollection())
            ->add(
                $this->generateCriterionEvaluation(
                    $productId,
                    'consistency_spelling',
                    CriterionEvaluationStatus::DONE,
                    $evaluateSpellingRates,
                    $evaluateSpellingStatus,
                    $evaluateSpellingData
                )
            );
    }

    private function generateCriterionEvaluation(ProductUuid $productId, string $code, string $status, ChannelLocaleRateCollection $resultRates, CriterionEvaluationResultStatusCollection $resultStatusCollection, array $resultData)
    {
        return new CriterionEvaluation(
            new CriterionCode($code),
            $productId,
            new \DateTimeImmutable(),
            new CriterionEvaluationStatus($status),
            new CriterionEvaluationResult($resultRates, $resultStatusCollection, $resultData)
        );
    }
}
