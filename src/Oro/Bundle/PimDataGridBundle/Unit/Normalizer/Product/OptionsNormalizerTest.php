<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Normalizer\Product;

use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\PimDataGridBundle\Normalizer\Product\OptionsNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OptionsNormalizerTest extends TestCase
{
    private IdentifiableObjectRepositoryInterface|MockObject $attributeOptionRepository;
    private OptionsNormalizer $sut;

    protected function setUp(): void
    {
        $this->attributeOptionRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->sut = new OptionsNormalizer($this->attributeOptionRepository);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(OptionsNormalizer::class, $this->sut);
    }

    public function test_it_is_a_normalizer(): void
    {
        $this->assertInstanceOf(\Symfony\Component\Serializer\Normalizer\NormalizerInterface::class, $this->sut);
    }

    public function test_it_supports_datagrid_format_and_product_value(): void
    {
        $value = $this->createMock(OptionsValueInterface::class);

        $this->assertSame(true, $this->sut->supportsNormalization($value, 'datagrid'));
        $this->assertSame(false, $this->sut->supportsNormalization($value, 'other_format'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'other_format'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'datagrid'));
    }

    public function test_it_normalizes_a_multi_select_product_value(): void
    {
        $value = $this->createMock(OptionsValueInterface::class);
        $colorBlue = $this->createMock(AttributeOptionInterface::class);
        $colorRed = $this->createMock(AttributeOptionInterface::class);
        $optionValueBlue = $this->createMock(AttributeOptionValueInterface::class);
        $optionValueRed = $this->createMock(AttributeOptionValueInterface::class);

        $value->method('getAttributeCode')->willReturn('color');
        $value->method('getData')->willReturn(['blue', 'red']);
        $this->attributeOptionRepository->method('findOneByIdentifier')->with('color.blue')->willReturn($colorBlue);
        $this->attributeOptionRepository->method('findOneByIdentifier')->with('color.red')->willReturn($colorRed);
        $colorRed->method('getTranslation')->with('fr_FR')->willReturn($optionValueRed);
        $colorRed->method('getCode')->willReturn('red');
        $colorBlue->method('getTranslation')->with('fr_FR')->willReturn($optionValueBlue);
        $optionValueBlue->method('getValue')->willReturn('Blue');
        $optionValueRed->method('getValue')->willReturn(null);
        $value->method('getLocaleCode')->willReturn(null);
        $value->method('getScopeCode')->willReturn(null);
        $data =  [
                    'locale' => null,
                    'scope'  => null,
                    'data'   => 'Blue, [red]',
                ];
        $this->assertSame($data, $this->sut->normalize($value, 'datagrid', ['data_locale' => 'fr_FR']));
    }
}
