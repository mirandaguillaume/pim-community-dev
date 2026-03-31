<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyTranslationInterface;
use Oro\Bundle\PimDataGridBundle\Normalizer\ProductAssociationNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

interface SerializerNormalizerInterface extends SerializerInterface, NormalizerInterface
{
}

class ProductAssociationNormalizerTest extends TestCase
{
    private SerializerNormalizerInterface|MockObject $serializer;
    private ImageNormalizer|MockObject $imageNormalizer;
    private GetProductCompletenesses|MockObject $getProductCompletenesses;
    private ProductAssociationNormalizer $sut;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerNormalizerInterface::class);
        $this->imageNormalizer = $this->createMock(ImageNormalizer::class);
        $this->getProductCompletenesses = $this->createMock(GetProductCompletenesses::class);
        $this->sut = new ProductAssociationNormalizer($this->imageNormalizer, $this->getProductCompletenesses);
        $this->sut->setSerializer($this->serializer);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductAssociationNormalizer::class, $this->sut);
        $this->assertInstanceOf(SerializerAwareInterface::class, $this->sut);
    }

    public function test_it_is_a_normalizer(): void
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->sut);
    }

    public function test_it_supports_datagrid_format_and_product_value(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $this->assertSame(true, $this->sut->supportsNormalization($product, 'datagrid'));
        $this->assertSame(false, $this->sut->supportsNormalization($product, 'other_format'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'other_format'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'datagrid'));
    }

    public function test_it_normalizes_a_product_with_label(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $family = $this->createMock(FamilyInterface::class);
        $familyEN = $this->createMock(FamilyTranslationInterface::class);
        $currentProduct = $this->createMock(ProductInterface::class);
        $image = $this->createMock(ValueInterface::class);

        $context = [
                    'locales'             => ['en_US'],
                    'data_locale'         => 'en_US',
                    'data_channel'        => 'ecommerce',
                    'channels'            => ['ecommerce'],
                    'current_product'     => $currentProduct,
                    'association_type_id' => 1,
                    'is_associated'       => false,
                ];
        $currentProduct->method('getAssociations')->willReturn([]);
        $product->method('getFamily')->willReturn($family);
        $product->method('getUuid')->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $family->method('getCode')->willReturn('tshirt');
        $family->method('getTranslation')->with('en_US')->willReturn($familyEN);
        $familyEN->method('getLabel')->willReturn('Tshirt');
        $product->method('getIdentifier')->willReturn('purple_tshirt');
        $product->method('isEnabled')->willReturn(true);
        $created = new \DateTime('2017-01-01T01:03:34+01:00');
        $product->method('getCreated')->willReturn($created);
        $this->serializer->method('normalize')->willReturnCallback(function ($object) {
            if ($object instanceof \DateTime) {
                return $object->format('Y-m-d\TH:i:sP');
            }
            return null;
        });
        $updated = new \DateTime('2017-01-01T01:04:34+01:00');
        $product->method('getUpdated')->willReturn($updated);
        $product->method('getLabel')->with('en_US', 'ecommerce')->willReturn('Purple tshirt');
        $this->getProductCompletenesses->method('fromProductUuid')->with(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'))->willReturn(new ProductCompletenessCollection(
            Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'),
            [
                            new ProductCompleteness('ecommerce', 'en_US', 10, 1),
                        ]
        ));
        $product->method('getImage')->willReturn($image);
        $this->imageNormalizer->method('normalize')->with($image, 'en_US', 'ecommerce')->willReturn([
                    'filePath' => '/p/i/m/4/all.png',
                    'originalFileName' => 'all.png',
                ]);
        $data = [
                    'identifier'    => 'purple_tshirt',
                    'family'        => 'Tshirt',
                    'enabled'       => true,
                    'created'       => '2017-01-01T01:03:34+01:00',
                    'updated'       => '2017-01-01T01:04:34+01:00',
                    'is_checked'    => false,
                    'is_associated' => false,
                    'label'         => 'Purple tshirt',
                    'completeness'  => 90,
                    'image'         => [
                        'filePath' => '/p/i/m/4/all.png',
                        'originalFileName' => 'all.png',
                    ],
                ];
        $this->assertSame($data, $this->sut->normalize($product, 'datagrid', $context));
    }

    public function test_it_normalizes_a_product_without_label(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $family = $this->createMock(FamilyInterface::class);
        $familyEN = $this->createMock(FamilyTranslationInterface::class);
        $currentProduct = $this->createMock(ProductInterface::class);
        $image = $this->createMock(ValueInterface::class);

        $context = [
                    'locales'             => ['en_US'],
                    'data_locale'         => 'en_US',
                    'data_channel'        => 'ecommerce',
                    'channels'            => ['ecommerce'],
                    'current_product'     => $currentProduct,
                    'association_type_id' => 1,
                    'is_associated'       => false,
                ];
        $currentProduct->method('getAssociations')->willReturn([]);
        $product->method('getFamily')->willReturn($family);
        $product->method('getUuid')->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $family->method('getCode')->willReturn('tshirt');
        $family->method('getTranslation')->with('en_US')->willReturn($familyEN);
        $familyEN->method('getLabel')->willReturn(null);
        $product->method('getIdentifier')->willReturn('purple_tshirt');
        $product->method('isEnabled')->willReturn(true);
        $created = new \DateTime('2017-01-01T01:03:34+01:00');
        $product->method('getCreated')->willReturn($created);
        $this->serializer->method('normalize')->willReturnCallback(function ($object) {
            if ($object instanceof \DateTime) {
                return $object->format('Y-m-d\TH:i:sP');
            }
            return null;
        });
        $updated = new \DateTime('2017-01-01T01:04:34+01:00');
        $product->method('getUpdated')->willReturn($updated);
        $product->method('getLabel')->with('en_US', 'ecommerce')->willReturn('Purple tshirt');
        $this->getProductCompletenesses->method('fromProductUuid')->with(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'))->willReturn(new ProductCompletenessCollection(
            Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'),
            [
                            new ProductCompleteness('ecommerce', 'en_US', 10, 1),
                        ]
        ));
        $product->method('getImage')->willReturn($image);
        $this->imageNormalizer->method('normalize')->with($image, 'en_US', 'ecommerce')->willReturn([
                    'filePath' => '/p/i/m/4/all.png',
                    'originalFileName' => 'all.png',
                ]);
        $data = [
                    'identifier'    => 'purple_tshirt',
                    'family'        => '[tshirt]',
                    'enabled'       => true,
                    'created'       => '2017-01-01T01:03:34+01:00',
                    'updated'       => '2017-01-01T01:04:34+01:00',
                    'is_checked'    => false,
                    'is_associated' => false,
                    'label'         => 'Purple tshirt',
                    'completeness'  => 90,
                    'image'         => [
                        'filePath' => '/p/i/m/4/all.png',
                        'originalFileName' => 'all.png',
                    ],
                ];
        $this->assertSame($data, $this->sut->normalize($product, 'datagrid', $context));
    }
}
