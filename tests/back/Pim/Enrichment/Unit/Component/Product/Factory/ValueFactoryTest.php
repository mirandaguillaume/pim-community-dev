<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Exception\NotLocalizableAndNotScopableAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory as SingleValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use OutOfBoundsException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValueFactoryTest extends TestCase
{
    private SingleValueFactory|MockObject $factory1;
    private SingleValueFactory|MockObject $factory2;
    private ValueFactory $sut;

    protected function setUp(): void
    {
        $this->factory1 = $this->createMock(SingleValueFactory::class);
        $this->factory2 = $this->createMock(SingleValueFactory::class);
        $this->factory1->method('supportedAttributeType')->willReturn('an_attribute_type1');
        $this->factory2->method('supportedAttributeType')->willReturn('an_attribute_type2');
        $this->sut = new ValueFactory([$this->factory1, $this->factory2]);
    }

    public function test_it_calls_the_right_factory_without_checking_data(): void
    {
        $value = $this->createMock(ValueInterface::class);

        $attribute = new Attribute('an_attribute', 'an_attribute_type2', [], false, false, null, null, false, 'backend_type', []);
        $this->factory2->method('createWithoutCheckingData')->with($attribute, null, null, 'data')->willReturn($value);
        $this->assertSame($value, $this->sut->createWithoutCheckingData($attribute, null, null, 'data'));
    }

    public function test_it_calls_the_right_factory_by_checking_data(): void
    {
        $value = $this->createMock(ValueInterface::class);

        $attribute = new Attribute('an_attribute', 'an_attribute_type2', [], false, false, null, null, false, 'backend_type', []);
        $this->factory2->method('createByCheckingData')->with($attribute, null, null, 'data')->willReturn($value);
        $this->assertSame($value, $this->sut->createByCheckingData($attribute, null, null, 'data'));
    }

    public function test_it_throws_an_exception_if_the_attribute_type_is_not_supported(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->sut->createWithoutCheckingData(new Attribute('an_attribute', 'non_supported_attribute_type', [], false, false, null, null, false, 'backend_type', []),
                        null,
                        null,
                        'data');
    }

    public function test_it_throws_an_exception_if_attribute_is_not_consistent_with_provided_locale_code_or_channel_code(): void
    {
        $this->expectException(NotLocalizableAndNotScopableAttributeException::class);
        $this->sut->createByCheckingData(new Attribute('an_attribute', 'an_attribute_type1', [], false, false, null, null, false, 'backend_type', []),
                        'ecommerce',
                        null,
                        'data');
    }
}
