<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationResultStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EvaluateImageEnrichment implements EvaluateCriterionInterface
{
    final public const CRITERION_CODE = 'enrichment_image';

    final public const CRITERION_COEFFICIENT = 2;

    private readonly CriterionCode $code;

    public function __construct(private readonly CalculateProductCompletenessInterface $completenessCalculator, private readonly GetLocalesByChannelQueryInterface $localesByChannelQuery)
    {
        $this->code = new CriterionCode(self::CRITERION_CODE);
    }

    public function evaluate(Write\CriterionEvaluation $criterionEvaluation, ProductValuesCollection $productValues): Write\CriterionEvaluationResult
    {
        $localesByChannel = $this->localesByChannelQuery->getChannelLocaleCollection();
        $completenessResult = $this->completenessCalculator->calculate($criterionEvaluation->getEntityId());

        $evaluationResult = new Write\CriterionEvaluationResult();
        foreach ($localesByChannel as $channelCode => $localeCodes) {
            foreach ($localeCodes as $localeCode) {
                $this->evaluateChannelLocaleRate($evaluationResult, $channelCode, $localeCode, $completenessResult);
            }
        }

        return $evaluationResult;
    }

    public function getCode(): CriterionCode
    {
        return $this->code;
    }

    private function evaluateChannelLocaleRate(
        Write\CriterionEvaluationResult $evaluationResult,
        ChannelCode $channelCode,
        LocaleCode $localeCode,
        Write\CompletenessCalculationResult $completenessResult
    ): void {
        $rate = $completenessResult->getRates()->getByChannelAndLocale($channelCode, $localeCode);

        if (null === $rate) {
            $evaluationResult->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::notApplicable());
            return;
        }

        $missingAttributes = $completenessResult->getMissingAttributes()->getByChannelAndLocale($channelCode, $localeCode);

        $attributesRates = [];

        if (null !== $missingAttributes) {
            foreach ($missingAttributes as $attributeCode) {
                $attributesRates[$attributeCode] = 0;
            }
        }

        // The score is 100 when there is at least one image uploaded, 0 otherwise
        if (!$rate->isPerfect() && $rate->toInt() > 0) {
            $rate = new Rate(100);
        }

        $evaluationResult
            ->addRate($channelCode, $localeCode, $rate)
            ->addStatus($channelCode, $localeCode, CriterionEvaluationResultStatus::done())
            ->addRateByAttributes($channelCode, $localeCode, $attributesRates)
        ;
    }

    public function getCoefficient(): int
    {
        return self::CRITERION_COEFFICIENT;
    }
}
