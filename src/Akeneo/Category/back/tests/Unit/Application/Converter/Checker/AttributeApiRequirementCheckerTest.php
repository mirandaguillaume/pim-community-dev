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

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(AttributeApiRequirementChecker::class, $this->sut);
        $this->assertInstanceOf(RequirementChecker::class, $this->sut);
    }

    public function testItShouldThrowAnExceptionWhenLocaleCompositeKeyIsMissing(): void
    {
        $compositeKey = 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030';
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check(
            [
                [
                    'data' => '',
                    'channel' => 'ecommerce',
                    'locale' => 'fr_FR',
                    'attribute_code' => $compositeKey,
                ],
            ],
        );
    }

    public function testItShouldThrowAnExceptionWhenLocaleCompositeKeyIsEmpty(): void
    {
        $compositeKey = 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030';
        $localeCompositeKey = '';
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check(
            [
                $localeCompositeKey => [
                    'data' => '',
                    'channel' => 'ecommerce',
                    'locale' => 'fr_FR',
                    'attribute_code' => $compositeKey,
                ],
            ],
        );
    }

    public function testItShouldThrowAnExceptionWhenAttributeKeyDataIsMissing(): void
    {
        $compositeKey = 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030';
        $localeCompositeKey = $compositeKey.AbstractValue::SEPARATOR.'fr_FR';
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check(
            [
                $localeCompositeKey => [
                    'channel' => 'ecommerce',
                    'locale' => 'fr_FR',
                    'attribute_code' => $compositeKey,
                ],
            ],
        );
    }

    public function testItShouldNotThrowAnExceptionWhenAttributeKeyDataIsEmpty(): void
    {
        $compositeKey = 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030';
        $localeCompositeKey = $compositeKey.AbstractValue::SEPARATOR.'fr_FR';
        $this->sut->check(
            [
                $localeCompositeKey => [
                    'data' => '',
                    'channel' => 'ecommerce',
                    'locale' => 'fr_FR',
                    'attribute_code' => $compositeKey,
                ],
            ],
        );
        $this->addToAssertionCount(1);
    }

    public function testItShouldThrowAnExceptionWhenAttributeKeyChannelIsMissing(): void
    {
        $compositeKey = 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030';
        $localeCompositeKey = $compositeKey.AbstractValue::SEPARATOR.'ecommerce';
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check(
            [
                $localeCompositeKey => [
                    'data' => 'Shoes',
                    'attribute_code' => $compositeKey,
                ],
            ],
        );
    }

    public function testItShouldThrowAnExceptionWhenAttributeKeyChannelIsEmpty(): void
    {
        $compositeKey = 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030';
        $localeCompositeKey = $compositeKey.AbstractValue::SEPARATOR.'ecommerce';
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check(
            [
                $localeCompositeKey => [
                    'data' => 'Shoes',
                    'channel' => '',
                    'locale' => 'fr_FR',
                    'attribute_code' => $compositeKey,
                ],
            ],
        );
    }

    public function testItShouldThrowAnExceptionWhenAttributeKeyLocaleIsMissing(): void
    {
        $compositeKey = 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030';
        $localeCompositeKey = $compositeKey.AbstractValue::SEPARATOR.'fr_FR';
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check(
            [
                $localeCompositeKey => [
                    'data' => 'Shoes',
                    'channel' => 'ecommerce',
                    'attribute_code' => $compositeKey,
                ],
            ],
        );
    }

    public function testItShouldThrowAnExceptionWhenAttributeKeyLocaleIsEmpty(): void
    {
        $compositeKey = 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030';
        $localeCompositeKey = $compositeKey.AbstractValue::SEPARATOR.'fr_FR';
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check(
            [
                $localeCompositeKey => [
                    'data' => 'Shoes',
                    'channel' => 'ecommerce',
                    'locale' => '',
                    'attribute_code' => $compositeKey,
                ],
            ],
        );
    }

    public function testItShouldThrowAnExceptionWhenAttributeCodeKeyIsMissing(): void
    {
        $compositeKey = 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030';
        $localeCompositeKey = $compositeKey.AbstractValue::SEPARATOR.'fr_FR';
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check(
            [
                $localeCompositeKey => [
                    'data' => 'Shoes',
                    'channel' => 'ecommerce',
                    'locale' => 'fr_FR',
                ],
            ],
        );
    }

    public function testItShouldThrowAnExceptionWhenAttributeCodeKeyIsEmpty(): void
    {
        $localeCompositeKey = 'title'
                    .AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030'
                    .AbstractValue::SEPARATOR.'fr_FR';
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->check(
            [
                $localeCompositeKey => [
                    'data' => 'Shoes',
                    'channel' => 'ecommerce',
                    'locale' => 'fr_FR',
                    'attribute_code' => '',
                ],
            ],
        );
    }
}
