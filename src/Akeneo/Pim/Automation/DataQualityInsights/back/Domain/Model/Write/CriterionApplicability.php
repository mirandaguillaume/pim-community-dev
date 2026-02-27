<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class CriterionApplicability
{
    public function __construct(private CriterionEvaluationResult $evaluationResult, private bool $isApplicable) {}

    public function getEvaluationResult(): CriterionEvaluationResult
    {
        return $this->evaluationResult;
    }

    /**
     * Determine if the criterion is applicable on at least one product value.
     */
    public function isApplicable(): bool
    {
        return $this->isApplicable;
    }
}
