<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ValidMetric;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ValidMetricValidator;
use Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ValidMetricValidatorTest extends TestCase
{
    private ValidMetricValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new ValidMetricValidator();
    }

}
