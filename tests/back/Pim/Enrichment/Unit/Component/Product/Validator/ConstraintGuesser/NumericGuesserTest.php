<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\NumericGuesser;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumeric;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NumericGuesserTest extends TestCase
{
    private NumericGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new NumericGuesser();
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
        $attribute->method('getType')->willReturn('pim_catalog_number');
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
        $this->assertCount(1, $constraints);
        $constraint = $constraints[0];
        $constraint->shouldBeAnInstanceOf(IsNumeric::class);
    }
}
