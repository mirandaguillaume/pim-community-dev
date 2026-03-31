<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaEvaluationRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\CriterionNotFoundException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CriteriaEvaluationRegistryTest extends TestCase
{
    public function test_it_throws_an_exception_if_an_evaluation_service_does_not_exist(): void
    {
        $sut = new CriteriaEvaluationRegistry([]);
        $this->expectException(CriterionNotFoundException::class);
        $sut->get(new CriterionCode('unknown_code'));
    }

    public function test_it_filters_non_accepted_services(): void
    {
        $evaluateCriterion = $this->createMock(EvaluateCriterionInterface::class);
        $evaluateCriterion->method('getCode')->willReturn(new CriterionCode('my_code'));

        $sut = new CriteriaEvaluationRegistry([$evaluateCriterion, new \stdClass()]);
        $this->assertSame($evaluateCriterion, $sut->get(new CriterionCode('my_code')));
    }

    public function test_it_gives_the_coefficient_of_a_given_criterion(): void
    {
        $evaluateCriterionA = $this->createMock(EvaluateCriterionInterface::class);
        $evaluateCriterionA->method('getCode')->willReturn(new CriterionCode('criterion_A'));
        $evaluateCriterionA->method('getCoefficient')->willReturn(1);

        $evaluateCriterionB = $this->createMock(EvaluateCriterionInterface::class);
        $evaluateCriterionB->method('getCode')->willReturn(new CriterionCode('criterion_B'));

        $sut = new CriteriaEvaluationRegistry([$evaluateCriterionA, $evaluateCriterionB]);
        $this->assertSame(1, $sut->getCriterionCoefficient(new CriterionCode('criterion_A')));
    }
}
