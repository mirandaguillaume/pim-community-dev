<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Normalizer;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupTranslationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyTranslationInterface;
use Oro\Bundle\PimDataGridBundle\Normalizer\ProductNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductNormalizerTest extends TestCase
{
    private NormalizerInterface|MockObject $normalizer;
    private CollectionFilterInterface|MockObject $filter;
    private ImageNormalizer|MockObject $imageNormalizer;
    private GetProductCompletenesses|MockObject $getProductCompletenesses;
    private ProductNormalizer $sut;

    protected function setUp(): void
    {
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->filter = $this->createMock(CollectionFilterInterface::class);
        $this->imageNormalizer = $this->createMock(ImageNormalizer::class);
        $this->getProductCompletenesses = $this->createMock(GetProductCompletenesses::class);
        $this->sut = new ProductNormalizer($this->filter, $this->imageNormalizer, $this->getProductCompletenesses);
        $this->sut->setNormalizer($this->normalizer);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductNormalizer::class, $this->sut);
        $this->assertInstanceOf(NormalizerAwareInterface::class, $this->sut);
    }

    public function test_it_is_a_normalizer(): void
    {
        $this->assertInstanceOf(\Symfony\Component\Serializer\Normalizer\NormalizerInterface::class, $this->sut);
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
        $promotion = $this->createMock(GroupInterface::class);
        $promotionEN = $this->createMock(GroupTranslationInterface::class);
        $family = $this->createMock(FamilyInterface::class);
        $familyEN = $this->createMock(FamilyTranslationInterface::class);
        $values = $this->createMock(WriteValueCollection::class);
        $image = $this->createMock(ValueInterface::class);

        $context = [
                    'filter_types' => ['pim.transform.product_value.structured'],
                    'locales'      => ['en_US'],
                    'channels'     => ['ecommerce'],
                    'data_locale'  => 'en_US',
                    'data_channel' => 'ecommerce',
                ];
        $product->method('isVariant')->willReturn(false);
        $product->method('getUuid')->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $this->filter->method('filterCollection')->with($values, 'pim.transform.product_value.structured', $context)->willReturn($values);
        $product->method('getGroups')->willReturn([$promotion]);
        $promotion->method('getCode')->willReturn('promotion');
        $promotion->method('getTranslation')->with('en_US')->willReturn($promotionEN);
        $promotionEN->method('getLabel')->willReturn('Promotion');
        $product->method('getFamily')->willReturn($family);
        $family->method('getCode')->willReturn('tshirt');
        $family->method('getTranslation')->with('en_US')->willReturn($familyEN);
        $familyEN->method('getLabel')->willReturn('Tshirt');
        $product->method('getIdentifier')->willReturn('purple_tshirt');
        $product->method('isEnabled')->willReturn(true);
        $product->method('getValues')->willReturn($values);
        $this->normalizer->method('normalize')->willReturnCallback(function ($object) {
            if ($object instanceof WriteValueCollection) {
                return [
                    'text' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'my text',
                        ],
                    ],
                ];
            }
            if ($object instanceof \DateTime) {
                return $object->format('Y-m-d\TH:i:sP');
            }
            return null;
        });
        $created = new \DateTime('2017-01-01T01:03:34+01:00');
        $product->method('getCreated')->willReturn($created);
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
                    'filePath'         => '/p/i/m/4/all.png',
                    'originalFileName' => 'all.png',
                ]);
        $data = [
                    'identifier'   => 'purple_tshirt',
                    'family'       => 'Tshirt',
                    'groups'       => 'Promotion',
                    'enabled'      => true,
                    'values'       => [
                        'text' => [
                            [
                                'locale' => null,
                                'scope'  => null,
                                'data'   => 'my text',
                            ],
                        ],
                    ],
                    'created'      => '2017-01-01T01:03:34+01:00',
                    'updated'      => '2017-01-01T01:04:34+01:00',
                    'label'        => 'Purple tshirt',
                    'image'        => [
                        'filePath'         => '/p/i/m/4/all.png',
                        'originalFileName' => 'all.png',
                    ],
                    'completeness' => 90,
                    'document_type' => 'product',
                    'technical_id' => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
                    'search_id' => 'product_54162e35-ff81-48f1-96d5-5febd3f00fd5',
                    'is_checked' => false,
                    'complete_variant_product' => null,
                    'parent' => null,
                ];
        $this->assertSame($data, $this->sut->normalize($product, 'datagrid', $context));
    }

    public function test_it_normalizes_a_product_without_label(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $promotion = $this->createMock(GroupInterface::class);
        $promotionEN = $this->createMock(GroupTranslationInterface::class);
        $family = $this->createMock(FamilyInterface::class);
        $familyEN = $this->createMock(FamilyTranslationInterface::class);
        $productValues = $this->createMock(WriteValueCollection::class);
        $localeEN = $this->createMock(LocaleInterface::class);
        $channelEcommerce = $this->createMock(ChannelInterface::class);
        $image = $this->createMock(ValueInterface::class);

        $context = [
                    'filter_types' => ['pim.transform.product_value.structured'],
                    'locales'      => ['en_US'],
                    'channels'     => ['ecommerce'],
                    'data_locale'  => 'en_US',
                    'data_channel' => 'ecommerce',
                ];
        $product->method('isVariant')->willReturn(false);
        $product->method('getUuid')->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $this->filter->method('filterCollection')->with($productValues, 'pim.transform.product_value.structured', $context)->willReturn($productValues);
        $product->method('getGroups')->willReturn([$promotion]);
        $promotion->method('getCode')->willReturn('promotion');
        $promotion->method('getTranslation')->with('en_US')->willReturn($promotionEN);
        $promotionEN->method('getLabel')->willReturn(null);
        $product->method('getFamily')->willReturn($family);
        $family->method('getCode')->willReturn('tshirt');
        $family->method('getTranslation')->with('en_US')->willReturn($familyEN);
        $familyEN->method('getLabel')->willReturn(null);
        $product->method('getIdentifier')->willReturn('purple_tshirt');
        $product->method('isEnabled')->willReturn(true);
        $product->method('getValues')->willReturn($productValues);
        $this->normalizer->method('normalize')->willReturnCallback(function ($object) {
            if ($object instanceof WriteValueCollection) {
                return [
                    'text' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'my text',
                        ],
                    ],
                ];
            }
            if ($object instanceof \DateTime) {
                return $object->format('Y-m-d\TH:i:sP');
            }
            return null;
        });
        $created = new \DateTime('2017-01-01T01:03:34+01:00');
        $product->method('getCreated')->willReturn($created);
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
                    'filePath'         => '/p/i/m/4/all.png',
                    'originalFileName' => 'all.png',
                ]);
        $data = [
                    'identifier'   => 'purple_tshirt',
                    'family'       => '[tshirt]',
                    'groups'       => '[promotion]',
                    'enabled'      => true,
                    'values'       => [
                        'text' => [
                            [
                                'locale' => null,
                                'scope'  => null,
                                'data'   => 'my text',
                            ],
                        ],
                    ],
                    'created'      => '2017-01-01T01:03:34+01:00',
                    'updated'      => '2017-01-01T01:04:34+01:00',
                    'label'        => 'Purple tshirt',
                    'image'        => [
                        'filePath'         => '/p/i/m/4/all.png',
                        'originalFileName' => 'all.png',
                    ],
                    'completeness' => 90,
                    'document_type' => 'product',
                    'technical_id' => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
                    'search_id' => 'product_54162e35-ff81-48f1-96d5-5febd3f00fd5',
                    'is_checked' => false,
                    'complete_variant_product' => null,
                    'parent' => null,
                ];
        $this->assertSame($data, $this->sut->normalize($product, 'datagrid', $context));
    }

    public function test_it_normalizes_a_product_with_parent(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $productModel = $this->createMock(ProductModelInterface::class);
        $promotion = $this->createMock(GroupInterface::class);
        $promotionEN = $this->createMock(GroupTranslationInterface::class);
        $family = $this->createMock(FamilyInterface::class);
        $familyEN = $this->createMock(FamilyTranslationInterface::class);
        $productValues = $this->createMock(WriteValueCollection::class);
        $image = $this->createMock(ValueInterface::class);

        $context = [
                    'filter_types' => ['pim.transform.product_value.structured'],
                    'locales'      => ['en_US'],
                    'channels'     => ['ecommerce'],
                    'data_locale'  => 'en_US',
                    'data_channel' => 'ecommerce',
                ];
        $productModel->method('getCode')->willReturn('parent_code');
        $product->method('getParent')->willReturn($productModel);
        $product->method('isVariant')->willReturn(true);
        $product->method('getUuid')->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $this->filter->method('filterCollection')->with($productValues, 'pim.transform.product_value.structured', $context)->willReturn($productValues);
        $product->method('getGroups')->willReturn([$promotion]);
        $promotion->method('getCode')->willReturn('promotion');
        $promotion->method('getTranslation')->with('en_US')->willReturn($promotionEN);
        $promotionEN->method('getLabel')->willReturn(null);
        $product->method('getFamily')->willReturn($family);
        $family->method('getCode')->willReturn('tshirt');
        $family->method('getTranslation')->with('en_US')->willReturn($familyEN);
        $familyEN->method('getLabel')->willReturn(null);
        $product->method('getIdentifier')->willReturn('purple_tshirt');
        $product->method('isEnabled')->willReturn(true);
        $product->method('getValues')->willReturn($productValues);
        $this->normalizer->method('normalize')->willReturnCallback(function ($object) {
            if ($object instanceof WriteValueCollection) {
                return [
                    'text' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'my text',
                        ],
                    ],
                ];
            }
            if ($object instanceof \DateTime) {
                return $object->format('Y-m-d\TH:i:sP');
            }
            return null;
        });
        $created = new \DateTime('2017-01-01T01:03:34+01:00');
        $product->method('getCreated')->willReturn($created);
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
                    'filePath'         => '/p/i/m/4/all.png',
                    'originalFileName' => 'all.png',
                ]);
        $data = [
                    'identifier'   => 'purple_tshirt',
                    'family'       => '[tshirt]',
                    'groups'       => '[promotion]',
                    'enabled'      => true,
                    'values'       => [
                        'text' => [
                            [
                                'locale' => null,
                                'scope'  => null,
                                'data'   => 'my text',
                            ],
                        ],
                    ],
                    'created'      => '2017-01-01T01:03:34+01:00',
                    'updated'      => '2017-01-01T01:04:34+01:00',
                    'label'        => 'Purple tshirt',
                    'image'        => [
                        'filePath'         => '/p/i/m/4/all.png',
                        'originalFileName' => 'all.png',
                    ],
                    'completeness' => 90,
                    'document_type' => 'product',
                    'technical_id' => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
                    'search_id' => 'product_54162e35-ff81-48f1-96d5-5febd3f00fd5',
                    'is_checked' => false,
                    'complete_variant_product' => null,
                    'parent' => 'parent_code',
                ];
        $this->assertSame($data, $this->sut->normalize($product, 'datagrid', $context));
    }

    public function test_it_normalizes_a_product_without_locale_and_channel_context(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $promotion = $this->createMock(GroupInterface::class);
        $promotionEN = $this->createMock(GroupTranslationInterface::class);
        $family = $this->createMock(FamilyInterface::class);
        $familyEN = $this->createMock(FamilyTranslationInterface::class);
        $productValues = $this->createMock(WriteValueCollection::class);
        $image = $this->createMock(ValueInterface::class);

        $context = [
                    'filter_types' => ['pim.transform.product_value.structured'],
                    'locales' => ['en_US'],
                    'channels' => ['ecommerce'],
                ];
        $product->method('isVariant')->willReturn(false);
        $product->method('getUuid')->willReturn(Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'));
        $this->filter->method('filterCollection')->with($productValues, 'pim.transform.product_value.structured', $context)->willReturn($productValues);
        $product->method('getGroups')->willReturn([$promotion]);
        $promotion->method('getCode')->willReturn('promotion');
        $promotion->method('getTranslation')->with('en_US')->willReturn($promotionEN);
        $promotionEN->method('getLabel')->willReturn(null);
        $product->method('getFamily')->willReturn($family);
        $family->method('getCode')->willReturn('tshirt');
        $family->method('getTranslation')->with('en_US')->willReturn($familyEN);
        $familyEN->method('getLabel')->willReturn(null);
        $product->method('getIdentifier')->willReturn('purple_tshirt');
        $product->method('isEnabled')->willReturn(true);
        $product->method('getValues')->willReturn($productValues);
        $this->normalizer->method('normalize')->willReturnCallback(function ($object) {
            if ($object instanceof WriteValueCollection) {
                return [
                    'text' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'my text',
                        ],
                    ],
                ];
            }
            if ($object instanceof \DateTime) {
                return $object->format('Y-m-d\TH:i:sP');
            }
            return null;
        });
        $created = new \DateTime('2017-01-01T01:03:34+01:00');
        $product->method('getCreated')->willReturn($created);
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
        $this->imageNormalizer->method('normalize')->with($image, null, null)->willReturn([
                    'filePath' => '/p/i/m/4/all.png',
                    'originalFileName' => 'all.png',
                ]);
        $data = [
                    'identifier' => 'purple_tshirt',
                    'family' => '[tshirt]',
                    'groups' => '[promotion]',
                    'enabled' => true,
                    'values' => [
                        'text' => [
                            [
                                'locale' => null,
                                'scope' => null,
                                'data' => 'my text',
                            ],
                        ],
                    ],
                    'created' => '2017-01-01T01:03:34+01:00',
                    'updated' => '2017-01-01T01:04:34+01:00',
                    'label' => 'Purple tshirt',
                    'image' => [
                        'filePath' => '/p/i/m/4/all.png',
                        'originalFileName' => 'all.png',
                    ],
                    'completeness' => 90,
                    'document_type' => 'product',
                    'technical_id' => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
                    'search_id' => 'product_54162e35-ff81-48f1-96d5-5febd3f00fd5',
                    'is_checked' => false,
                    'complete_variant_product' => null,
                    'parent' => null,
                ];
        $this->assertSame($data, $this->sut->normalize($product, 'datagrid', $context));
    }
}
