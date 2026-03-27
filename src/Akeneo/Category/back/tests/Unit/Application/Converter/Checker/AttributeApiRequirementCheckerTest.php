<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Application\Converter\Checker;

use Akeneo\Category\Application\Converter\Checker\AttributeApiRequirementChecker;
use Akeneo\Category\Application\Converter\Checker\RequirementChecker;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Infrastructure\Exception\StructureArrayConversionException;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeApiRequirementCheckerTest extends TestCase
{
    private AttributeApiRequirementChecker $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeApiRequirementChecker();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AttributeApiRequirementChecker::class, $this->sut);
        $this->assertInstanceOf(RequirementChecker::class, $this->sut);
    }

    public function test_it_should_throw_an_exception_when_locale_composite_key_is_missing(): void
    {
        $compositeKey = "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check(
            [
                [
                    "data" => "",
                    "channel" => "ecommerce",
                    "locale" => "fr_FR",
                    "attribute_code" => $compositeKey,
                ],
            ]
        );
    }

    public function test_it_should_throw_an_exception_when_locale_composite_key_is_empty(): void
    {
        $compositeKey = "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = "";
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check(
            [
                $localeCompositeKey => [
                    "data" => "",
                    "channel" => "ecommerce",
                    "locale" => "fr_FR",
                    "attribute_code" => $compositeKey,
                ],
            ]
        );
    }

    public function test_it_should_throw_an_exception_when_attribute_key_data_is_missing(): void
    {
        $compositeKey = "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . AbstractValue::SEPARATOR . 'fr_FR';
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check(
            [
                $localeCompositeKey => [
                    "channel" => "ecommerce",
                    "locale" => "fr_FR",
                    "attribute_code" => $compositeKey,
                ],
            ]
        );
    }

    public function test_it_should_not_throw_an_exception_when_attribute_key_data_is_empty(): void
    {
        $compositeKey = "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . AbstractValue::SEPARATOR . 'fr_FR';
        $this->sut->check(
            [
                $localeCompositeKey => [
                    "data" => "",
                    "channel" => "ecommerce",
                    "locale" => "fr_FR",
                    "attribute_code" => $compositeKey,
                ],
            ]
        );
        $this->addToAssertionCount(1);
    }

    public function test_it_should_throw_an_exception_when_attribute_key_channel_is_missing(): void
    {
        $compositeKey = "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . AbstractValue::SEPARATOR . 'ecommerce';
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check(
            [
                $localeCompositeKey => [
                    "data" => "Shoes",
                    "attribute_code" => $compositeKey,
                ],
            ]
        );
    }

    public function test_it_should_throw_an_exception_when_attribute_key_channel_is_empty(): void
    {
        $compositeKey = "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . AbstractValue::SEPARATOR . 'ecommerce';
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check(
            [
                $localeCompositeKey => [
                    "data" => "Shoes",
                    "channel" => "",
                    "attribute_code" => $compositeKey,
                ],
            ]
        );
    }

    public function test_it_should_throw_an_exception_when_attribute_key_locale_is_missing(): void
    {
        $compositeKey = "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . AbstractValue::SEPARATOR . 'fr_FR';
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check(
            [
                $localeCompositeKey => [
                    "data" => "Shoes",
                    "attribute_code" => $compositeKey,
                ],
            ]
        );
    }

    public function test_it_should_throw_an_exception_when_attribute_key_locale_is_empty(): void
    {
        $compositeKey = "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . AbstractValue::SEPARATOR . 'fr_FR';
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check(
            [
                $localeCompositeKey => [
                    "data" => "Shoes",
                    "locale" => "",
                    "attribute_code" => $compositeKey,
                ],
            ]
        );
    }

    public function test_it_should_throw_an_exception_when_attribute_code_key_is_missing(): void
    {
        $compositeKey = "title" . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030";
        $localeCompositeKey = $compositeKey . AbstractValue::SEPARATOR . 'fr_FR';
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check(
            [
                $localeCompositeKey => [
                    "data" => "Shoes",
                    "locale" => "fr_FR",
                ],
            ]
        );
    }

    public function test_it_should_throw_an_exception_when_attribute_code_key_is_empty(): void
    {
        $localeCompositeKey = "title"
                    . AbstractValue::SEPARATOR . "87939c45-1d85-4134-9579-d594fff65030"
                    . AbstractValue::SEPARATOR . 'fr_FR';
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check(
            [
                $localeCompositeKey => [
                    "data" => "Shoes",
                    "locale" => "fr_FR",
                    "attribute_code" => "",
                ],
            ]
        );
    }
}
