<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Normalizer\Product;

use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\PimDataGridBundle\Normalizer\Product\OptionNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OptionNormalizerTest extends TestCase
{
    private IdentifiableObjectRepositoryInterface|MockObject $attributeOptionRepository;
    private OptionNormalizer $sut;

    protected function setUp(): void
    {
        $this->attributeOptionRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->sut = new OptionNormalizer($this->attributeOptionRepository);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(OptionNormalizer::class, $this->sut);
    }

    public function test_it_is_a_normalizer(): void
    {
        $this->assertInstanceOf(\Symfony\Component\Serializer\Normalizer\NormalizerInterface::class, $this->sut);
    }

    public function test_it_supports_datagrid_format_and_product_value(): void
    {
        $value = $this->createMock(OptionValueInterface::class);

        $this->assertSame(true, $this->sut->supportsNormalization($value, 'datagrid'));
        $this->assertSame(false, $this->sut->supportsNormalization($value, 'other_format'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'other_format'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'datagrid'));
    }

    public function test_it_normalizes_a_simple_select_product_value_with_label(): void
    {
        $value = $this->createMock(OptionValueInterface::class);
        $purpleOption = $this->createMock(AttributeOptionInterface::class);
        $purpleOptionValue = $this->createMock(AttributeOptionValueInterface::class);

        $value->method('getAttributeCode')->willReturn('color');
        $value->method('getData')->willReturn('purple');
        $value->method('getLocaleCode')->willReturn(null);
        $value->method('getScopeCode')->willReturn(null);
        $this->attributeOptionRepository->method('findOneByIdentifier')->with('color.purple')->willReturn($purpleOption);
        $purpleOption->expects($this->once())->method('setLocale')->with('fr_FR');
        $purpleOption->method('getTranslation')->willReturn($purpleOptionValue);
        $purpleOptionValue->method('getValue')->willReturn('Violet');
        $data =  [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'Violet',
                ];
        $this->assertSame($data, $this->sut->normalize($value, 'datagrid', ['data_locale' => 'fr_FR']));
    }

    public function test_it_normalizes_an_simple_select_product_value_without_label(): void
    {
        $value = $this->createMock(OptionValueInterface::class);
        $purpleOption = $this->createMock(AttributeOptionInterface::class);
        $purpleOptionValue = $this->createMock(AttributeOptionValueInterface::class);

        $value->method('getAttributeCode')->willReturn('color');
        $value->method('getData')->willReturn('purple');
        $value->method('getLocaleCode')->willReturn(null);
        $value->method('getScopeCode')->willReturn(null);
        $this->attributeOptionRepository->method('findOneByIdentifier')->with('color.purple')->willReturn($purpleOption);
        $purpleOption->expects($this->once())->method('setLocale')->with('fr_FR');
        $purpleOption->method('getTranslation')->willReturn($purpleOptionValue);
        $purpleOption->method('getCode')->willReturn('purple');
        $purpleOptionValue->method('getValue')->willReturn(null);
        $purpleOptionValue->method('getValue')->willReturn(null);
        $data =  [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => '[purple]',
                ];
        $this->assertSame($data, $this->sut->normalize($value, 'datagrid', ['data_locale' => 'fr_FR']));
    }

    public function test_it_normalizes_a_simple_select_product_value_without_data(): void
    {
        $value = $this->createMock(OptionValueInterface::class);
        $purpleOption = $this->createMock(AttributeOptionInterface::class);

        $value->method('getAttributeCode')->willReturn('color');
        $value->method('getData')->willReturn(null);
        $value->method('getLocaleCode')->willReturn(null);
        $value->method('getScopeCode')->willReturn(null);
        $purpleOption->expects($this->never())->method('setLocale')->with($this->anything());
        $purpleOption->expects($this->never())->method('getTranslation');
        $data =  [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => '',
                ];
        $this->assertSame($data, $this->sut->normalize($value, 'datagrid', ['data_locale' => 'fr_FR']));
    }
}
