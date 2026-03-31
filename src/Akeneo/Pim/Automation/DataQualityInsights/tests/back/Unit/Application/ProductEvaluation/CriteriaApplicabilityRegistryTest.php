<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaApplicabilityRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionApplicabilityInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CriteriaApplicabilityRegistryTest extends TestCase
{
    public function test_it_returns_no_criterion_codes_if_no_services_are_injected(): void
    {
        $sut = new CriteriaApplicabilityRegistry([]);
        $this->assertSame([], $sut->getCriterionCodes());
    }

    public function test_it_returns_null_if_an_applicability_service_does_not_exist(): void
    {
        $evaluateCriterionApplicability = $this->createMock(EvaluateCriterionApplicabilityInterface::class);
        $evaluateCriterionApplicability->method('getCode')->willReturn(new CriterionCode('my_code'));

        $sut = new CriteriaApplicabilityRegistry([$evaluateCriterionApplicability]);
        $this->assertNull($sut->get(new CriterionCode('unknown_code')));
    }

    public function test_it_filters_non_accepted_services(): void
    {
        $evaluateCriterionApplicability = $this->createMock(EvaluateCriterionApplicabilityInterface::class);
        $evaluateCriterionApplicability->method('getCode')->willReturn(new CriterionCode('my_code'));

        $sut = new CriteriaApplicabilityRegistry([$evaluateCriterionApplicability, new \stdClass()]);
        $this->assertEquals([new CriterionCode('my_code')], $sut->getCriterionCodes());
        $this->assertSame($evaluateCriterionApplicability, $sut->get(new CriterionCode('my_code')));
    }
}
