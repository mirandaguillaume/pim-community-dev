<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Normalizer\Product;

use Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter\PresenterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\PimDataGridBundle\Normalizer\Product\ProductValuesNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

class ProductValuesNormalizerTest extends TestCase
{
    private SerializerInterface|MockObject $serializer;
    private PresenterRegistryInterface|MockObject $presenterRegistry;
    private UserContext|MockObject $userContext;
    private ProductValuesNormalizer $sut;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->presenterRegistry = $this->createMock(PresenterRegistryInterface::class);
        $this->userContext = $this->createMock(UserContext::class);
        $this->sut = new ProductValuesNormalizer($this->presenterRegistry, $this->userContext);
        $this->serializer->method('implement')->with(\Symfony\Component\Serializer\Normalizer\NormalizerInterface::class);
        $this->sut->setSerializer($this->serializer);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductValuesNormalizer::class, $this->sut);
    }

    public function test_it_is_a_normalizer(): void
    {
        $this->assertInstanceOf(\Symfony\Component\Serializer\Normalizer\NormalizerInterface::class, $this->sut);
        $this->assertInstanceOf(\Symfony\Component\Serializer\SerializerAwareInterface::class, $this->sut);
    }

    public function test_it_supports_datagrid_format_and_collection_values(): void
    {
        $attribute = new Attribute();
        $attribute->setCode('attribute');
        $attribute->setBackendType('text');
        $realValue = ScalarValue::value($attribute, null);
        $valuesCollection = new WriteValueCollection([$realValue]);
        $valuesArray = [$realValue];
        $emptyValuesCollection = new WriteValueCollection();
        $randomCollection = new ArrayCollection([new \stdClass()]);
        $randomArray = [new \stdClass()];
        $this->assertSame(true, $this->sut->supportsNormalization($valuesCollection, 'datagrid'));
        $this->assertSame(false, $this->sut->supportsNormalization($valuesArray, 'datagrid'));
        $this->assertSame(true, $this->sut->supportsNormalization($emptyValuesCollection, 'datagrid'));
        $this->assertSame(false, $this->sut->supportsNormalization($randomCollection, 'datagrid'));
        $this->assertSame(false, $this->sut->supportsNormalization($randomArray, 'datagrid'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'datagrid'));
        $this->assertSame(false, $this->sut->supportsNormalization($valuesCollection, 'other_format'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'other_format'));
    }

    public function test_it_normalizes_collection_of_product_values(): void
    {
        $textValue = $this->createMock(ValueInterface::class);
        $priceValue = $this->createMock(ValueInterface::class);
        $values = $this->createMock(WriteValueCollection::class);
        $valuesIterator = $this->createMock(ArrayIterator::class);
        $pricePresenter = $this->createMock(PresenterInterface::class);

        $values->method('getIterator')->willReturn($valuesIterator);
        $valuesIterator->expects($this->once())->method('rewind');
        $valuesIterator->method('valid')->willReturn(true, true, false);
        $valuesIterator->method('current')->willReturn($textValue, $priceValue);
        $valuesIterator->expects($this->once())->method('next');
        $textValue->method('getAttributeCode')->willReturn('text');
        $priceValue->method('getAttributeCode')->willReturn('price');
        $this->serializer->expects($this->once())->method('normalize')->with($textValue, 'datagrid', [])->willReturn(['locale' => null, 'scope' => null, 'data' => 'foo']);
        $prices = [
                    ['amount' => '12.50', 'currency' => 'USD'],
                    ['amount' => '15.00', 'currency' => 'EUR'],
                ];
        $this->serializer->expects($this->once())->method('normalize')->with($priceValue, 'datagrid', [])->willReturn(['locale' => 'en_US', 'scope' => 'ecommerce', 'data' => $prices]);
        $this->userContext->method('getUiLocaleCode')->willReturn('en_US');
        $this->presenterRegistry->method('getPresenterByAttributeCode')->with('text')->willReturn(null);
        $this->presenterRegistry->method('getPresenterByAttributeCode')->with('price')->willReturn($pricePresenter);
        $pricePresenter->method('present')->with($prices, ['locale' => 'en_US', 'attribute' => 'price'])->willReturn('$15.00, $12.50');
        $this->assertSame([
                            'text' => [
                                ['locale' => null, 'scope' => null, 'data' => 'foo'],
                            ],
                            'price' => [
                                ['locale' => 'en_US', 'scope' => 'ecommerce', 'data' => '$15.00, $12.50'],
                            ],
                        ], $this->sut->normalize($values, 'datagrid'));
    }
}
