<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser\UniqueValueGuesser;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UniqueValueGuesserTest extends TestCase
{
    private UniqueValueGuesser $sut;

    protected function setUp(): void
    {
        $this->sut = new UniqueValueGuesser();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(UniqueValueGuesser::class, $this->sut);
    }

    public function test_it_is_an_attribute_constraint_guesser(): void
    {
        $this->assertInstanceOf(ConstraintGuesserInterface::class, $this->sut);
    }

    public function test_it_enforces_attribute_type(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);

        foreach ($this->sut->dataProviderForSupportedAttributes() as $attributeType => $attributeTypeTest) {
                    $attributeBackendType = $attributeTypeTest[0];
                    $expectedResult = $attributeTypeTest[1];
                    $attribute->getBackendType()->willReturn($attributeBackendType);
                    $attribute->getType()->willReturn('pim_catalog_' . $attributeType);
                    $attribute->isMainIdentifier()->willReturn(false);
                    $this->assertSame($expectedResult, $this->sut->supportAttribute($attribute));
                }
    }

    public function test_it_does_not_guess_constraints_for_the_main_identifier_attribute(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getBackendType')->willReturn(AttributeTypes::BACKEND_TYPE_TEXT);
        $attribute->method('isUnique')->willReturn(true);
        $attribute->method('isMainIdentifier')->willReturn(true);
        $this->assertSame(false, $this->sut->supportAttribute($attribute));
        $this->assertSame([], $this->sut->guessConstraints($attribute));
    }

    public function test_it_guesses_constraints_for_unique_value(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getBackendType')->willReturn(AttributeTypes::BACKEND_TYPE_TEXT);
        $attribute->method('isUnique')->willReturn(true);
        $attribute->method('getType')->willReturn(AttributeTypes::TEXT);
        $attribute->method('isMainIdentifier')->willReturn(false);
        $this->assertEquals([new UniqueValue()], $this->sut->guessConstraints($attribute));
    }

    public function test_it_does_not_guess_constraints_for_non_unique_values(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getBackendType')->willReturn(AttributeTypes::BACKEND_TYPE_TEXT);
        $attribute->method('getType')->willReturn(AttributeTypes::TEXT);
        $attribute->method('isUnique')->willReturn(false);
        $this->assertSame([], $this->sut->guessConstraints($attribute));
    }

    public function test_it_changes_the_erro_message_for_identifier_attributes(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('isUnique')->willReturn(true);
        $attribute->method('getType')->willReturn(AttributeTypes::IDENTIFIER);
        $attribute->method('isMainIdentifier')->willReturn(false);
        $this->assertEquals([new UniqueValue(['message' => 'pim_catalog.constraint.unique_identifier_value'])], $this->sut->guessConstraints($attribute));
    }

    private function dataProviderForSupportedAttributes()
    {
            return [
                'boolean'    => [AttributeTypes::BACKEND_TYPE_BOOLEAN, false],
                'collection' => [AttributeTypes::BACKEND_TYPE_COLLECTION, false],
                'date'       => [AttributeTypes::BACKEND_TYPE_DATE, true],
                'datetime'   => [AttributeTypes::BACKEND_TYPE_DATETIME, true],
                'decimal'    => [AttributeTypes::BACKEND_TYPE_DECIMAL, true],
                'entity'     => [AttributeTypes::BACKEND_TYPE_ENTITY, false],
                'media'      => [AttributeTypes::BACKEND_TYPE_MEDIA, false],
                'metric'     => [AttributeTypes::BACKEND_TYPE_METRIC, false],
                'option'     => [AttributeTypes::BACKEND_TYPE_OPTION, false],
                'options'    => [AttributeTypes::BACKEND_TYPE_OPTIONS, false],
                'price'      => [AttributeTypes::BACKEND_TYPE_PRICE, false],
                'textarea'   => [AttributeTypes::BACKEND_TYPE_TEXTAREA, false],
                'text'       => [AttributeTypes::BACKEND_TYPE_TEXT, true],
            ];
        }
}
