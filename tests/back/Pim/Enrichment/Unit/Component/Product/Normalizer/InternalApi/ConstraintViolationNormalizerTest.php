<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ConstraintViolationNormalizer;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ValidDateRange;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ValidMetric;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;

class ConstraintViolationNormalizerTest extends TestCase
{
    private ConstraintViolationNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new ConstraintViolationNormalizer();
    }

    public function test_it_normalizes_violation_without_translating(): void
    {
        $violation = $this->createMock(ConstraintViolation::class);
        $constraint = $this->createMock(ValidDateRange::class);

        $violation->method('getPropertyPath')->willReturn('foo');
        $violation->method('getMessage')->willReturn('The max date must be greater than the min date.');
        $violation->method('getMessageTemplate')->willReturn('attribute_date_must_be_greater');
        $violation->method('getParameters')->willReturn([]);
        $violation->method('getInvalidValue')->willReturn('');
        $violation->method('getConstraint')->willReturn($constraint);
        $violation->method('getPlural')->willReturn(null);
        $this->assertSame([
                    'messageTemplate' => 'attribute_date_must_be_greater',
                    'parameters' => [],
                    'message' => 'The max date must be greater than the min date.',
                    'propertyPath' => 'foo',
                    'invalidValue' => '',
                    'plural' => null
                ], $this->sut->normalize($violation, 'internal_api', ['translate' => false]));
    }
}
