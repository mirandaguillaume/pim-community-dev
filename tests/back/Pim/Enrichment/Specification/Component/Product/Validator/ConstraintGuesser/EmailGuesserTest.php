<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\EmailGuesser;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Email;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EmailGuesserTest extends TestCase
{
    private EmailGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new EmailGuesser();
    }

    public function test_it_is_an_attribute_constraint_guesser(): void
    {
        $this->assertInstanceOf(ConstraintGuesserInterface::class, $this->sut);
    }

    public function test_it_supports_text_attributes(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getType')->willReturn('pim_catalog_text');
        $this->assertSame(true, $this->sut->supportAttribute($attribute));
    }

    public function test_it_does_not_support_other_attributes(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getType')->willReturn('pim_catalog_image');
        $this->assertSame(false, $this->sut->supportAttribute($attribute));
    }

    public function test_it_guesses_email(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->expects($this->once())->method('getValidationRule')->willReturn('email');
        $attribute->method('getCode')->willReturn('code');
        $constraints = $this->guessConstraints($attribute);
        $constraints->shouldHaveCount(1);
        $firstConstraint = $constraints[0];
        $firstConstraint->shouldBeAnInstanceOf(Email::class);
        $firstConstraint->attributeCode->shouldReturn('code');
    }

    public function test_it_does_not_guess_email(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->expects($this->once())->method('getValidationRule')->willReturn('not_email');
        $constraints = $this->guessConstraints($attribute);
        $constraints->shouldReturn([]);
    }
}
