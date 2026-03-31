<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\MetricGuesser;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumeric;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\ValidMetric;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MetricGuesserTest extends TestCase
{
    private MetricGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new MetricGuesser();
    }

    public function test_it_is_an_attribute_constraint_guesser(): void
    {
        $this->assertInstanceOf(ConstraintGuesserInterface::class, $this->sut);
    }

    public function test_it_enforces_attribute_type(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getType')->willReturn('pim_catalog_metric');
        $this->assertSame(true, $this->sut->supportAttribute($attribute));
        $attribute->method('getType')->willReturn('pim_catalog_text');
        $this->assertSame(false, $this->sut->supportAttribute($attribute));
        $attribute->method('getType')->willReturn('foo');
        $this->assertSame(false, $this->sut->supportAttribute($attribute));
    }

    public function test_it_always_guess(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getCode')->willReturn('');
        $constraints = $this->sut->guessConstraints($attribute);
        $this->assertCount(2, $constraints);
        $this->assertInstanceOf(ValidMetric::class, $constraints[0]);
        $this->assertInstanceOf(IsNumeric::class, $constraints[1]);
    }
}
