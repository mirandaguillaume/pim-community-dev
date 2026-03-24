<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CompleteEvaluationWithImprovableAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaByFeatureRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetCriteriaEvaluationsByEntityIdQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Return format:
 *
 * [
 *    channel => [
 *        locale => [
 *            [
 *                'code' => string  // code of the criterion,
 *                'status' => string // status of the criterion evaluation (done, not_applicable, error)
 *                'rate => [
 *                    'value' => float  // integer value of the criterion rate,
 *                    'rank' => string  // criterion rate letter,
 *                ],
 *                'improvable_attributes' => string[] // list of the code of the attributes to improve
 *            ],
 *        ],
 *    ]
 * ]
 */
class GetProductEvaluation
{
    public function __construct(
        private readonly GetCriteriaEvaluationsByEntityIdQueryInterface $getCriteriaEvaluationsByProductIdQuery,
        private readonly GetLocalesByChannelQueryInterface $getLocalesByChannelQuery,
        private readonly CriteriaByFeatureRegistry $criteriaRegistry,
        private readonly CompleteEvaluationWithImprovableAttributes $completeEvaluationWithImprovableAttributes
    ) {
    }

    public function get(ProductEntityIdInterface $productId): array
    {
        $criteriaEvaluations = $this->getCriteriaEvaluationsByProductIdQuery->execute($productId);
        $criteriaEvaluations = ($this->completeEvaluationWithImprovableAttributes)($criteriaEvaluations);
        $channelsLocales = $this->getLocalesByChannelQuery->getChannelLocaleCollection();

        $formattedProductEvaluation = [];

        foreach ($channelsLocales as $channelCode => $locales) {
            foreach ($locales as $localeCode) {
                $formattedProductEvaluation[strval($channelCode)][strval($localeCode)]
                    = $this->formatCriteriaEvaluations($criteriaEvaluations, $channelCode, $localeCode);
            }
        }

        return $formattedProductEvaluation;
    }

    private function formatCriteriaEvaluations(Read\CriterionEvaluationCollection $criteriaEvaluations, ChannelCode $channelCode, LocaleCode $localeCode): array
    {
        $criteriaRates = [];

        foreach ($this->criteriaRegistry->getEnabledCriterionCodes() as $criterionCode) {
            $criterionEvaluation = $criteriaEvaluations->get($criterionCode);
            $criteriaRates[] = $this->formatCriterionEvaluation(
                $criterionCode,
                $criterionEvaluation instanceof \Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluation ? $criterionEvaluation->getResult() : null,
                $channelCode,
                $localeCode
            );
        }

        return $criteriaRates;
    }

    private function formatCriterionEvaluation(CriterionCode $criterionCode, ?Read\CriterionEvaluationResult $evaluationResult, ChannelCode $channelCode, LocaleCode $localeCode): array
    {
        $rate = $evaluationResult instanceof \Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult ? $evaluationResult->getRates()->getByChannelAndLocale($channelCode, $localeCode) : null;
        $attributes = $evaluationResult instanceof \Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult ? $evaluationResult->getAttributes()->getByChannelAndLocale($channelCode, $localeCode) : [];
        $status = $evaluationResult instanceof \Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\CriterionEvaluationResult ? $evaluationResult->getStatus()->get($channelCode, $localeCode) : null;

        return [
            'code' => strval($criterionCode),
            'rate' => [
                'value' => $rate instanceof \Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate ? $rate->toInt() : null,
                'rank' => $rate instanceof \Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate ? $rate->toLetter() : null,
            ],
            'improvable_attributes' => $attributes ?? [],
            'status' => $status instanceof \Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus ? strval($status) : CriterionEvaluationResultStatus::IN_PROGRESS,
        ];
    }
}
