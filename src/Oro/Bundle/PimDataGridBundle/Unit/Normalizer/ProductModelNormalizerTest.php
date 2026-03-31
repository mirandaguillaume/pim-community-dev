<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Normalizer;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\ImageAsLabel;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\CompleteVariantProducts;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\VariantProductRatioInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyTranslationInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Oro\Bundle\PimDataGridBundle\Normalizer\ProductModelNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelNormalizerTest extends TestCase
{
    private NormalizerInterface|MockObject $normalizer;
    private CollectionFilterInterface|MockObject $filter;
    private VariantProductRatioInterface|MockObject $findVariantProductCompletenessQuery;
    private ImageAsLabel|MockObject $imageAsLabel;
    private ImageNormalizer|MockObject $imageNormalizer;
    private ProductModelNormalizer $sut;

    protected function setUp(): void
    {
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->filter = $this->createMock(CollectionFilterInterface::class);
        $this->findVariantProductCompletenessQuery = $this->createMock(VariantProductRatioInterface::class);
        $this->imageAsLabel = $this->createMock(ImageAsLabel::class);
        $this->imageNormalizer = $this->createMock(ImageNormalizer::class);
        $this->sut = new ProductModelNormalizer($this->filter, $this->findVariantProductCompletenessQuery, $this->imageAsLabel, $this->imageNormalizer);
        $this->sut->setNormalizer($this->normalizer);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductModelNormalizer::class, $this->sut);
        $this->assertInstanceOf(NormalizerAwareInterface::class, $this->sut);
    }

    public function test_it_is_a_normalizer(): void
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->sut);
    }

    public function test_it_supports_datagrid_format_and_product_value(): void
    {
        $product = $this->createMock(ProductModelInterface::class);

        $this->assertSame(true, $this->sut->supportsNormalization($product, 'datagrid'));
        $this->assertSame(false, $this->sut->supportsNormalization($product, 'other_format'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'other_format'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'datagrid'));
    }

    public function test_it_normalizes_a_product_model_with_label(): void
    {
        $productModel = $this->createMock(ProductModelInterface::class);
        $familyVariant = $this->createMock(FamilyVariantInterface::class);
        $family = $this->createMock(FamilyInterface::class);
        $familyEN = $this->createMock(FamilyTranslationInterface::class);
        $values = $this->createMock(WriteValueCollection::class);
        $localeEN = $this->createMock(LocaleInterface::class);
        $channelEcommerce = $this->createMock(ChannelInterface::class);
        $image = $this->createMock(ValueInterface::class);
        $completeness = $this->createMock(CompleteVariantProducts::class);

        $context = [
                    'filter_types' => ['pim.transform.product_value.structured'],
                    'locales'      => ['en_US'],
                    'channels'     => ['ecommerce'],
                    'data_locale'  => 'en_US',
                    'data_channel' => 'ecommerce',
                ];
        $productModel->method('getParent')->willReturn(null);
        $this->findVariantProductCompletenessQuery->method('findComplete')->with($productModel)->willReturn($completeness);
        $completeness->method('value')->with('ecommerce', 'en_US')->willReturn([
                    'complete' => 3,
                    'total' => 12,
                ]);
        $productModel->method('getId')->willReturn(78);
        $this->filter->method('filterCollection')->with($values, 'pim.transform.product_value.structured', $context)->willReturn($values);
        $productModel->method('getFamilyVariant')->willReturn($familyVariant);
        $familyVariant->method('getFamily')->willReturn($family);
        $family->method('getCode')->willReturn('tshirt');
        $family->method('getTranslation')->with('en_US')->willReturn($familyEN);
        $familyEN->method('getLabel')->willReturn('Tshirt');
        $productModel->method('getCode')->willReturn('purple_tshirt');
        $productModel->method('getValues')->willReturn($values);
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
        $productModel->method('getCreated')->willReturn($created);
        $updated = new \DateTime('2017-01-01T01:04:34+01:00');
        $productModel->method('getUpdated')->willReturn($updated);
        $productModel->method('getLabel')->with('en_US', 'ecommerce')->willReturn('Purple tshirt');
        $this->imageAsLabel->method('value')->with($productModel)->willReturn($image);
        $this->imageNormalizer->method('normalize')->with($image, 'en_US', 'ecommerce')->willReturn([
                    'filePath'         => '/p/i/m/4/all.png',
                    'originalFileName' => 'all.png',
                ]);
        $localeEN->method('getCode')->willReturn('en_US');
        $channelEcommerce->method('getCode')->willReturn('ecommerce');
        $data = [
                    'identifier' => 'purple_tshirt',
                    'family'     => 'Tshirt',
                    'values'     => [
                        'text' => [
                            [
                                'locale' => null,
                                'scope'  => null,
                                'data'   => 'my text',
                            ],
                        ],
                    ],
                    'created'    => '2017-01-01T01:03:34+01:00',
                    'updated'    => '2017-01-01T01:04:34+01:00',
                    'label'      => 'Purple tshirt',
                    'image'      => [
                        'filePath'         => '/p/i/m/4/all.png',
                        'originalFileName' => 'all.png',
                    ],
                    'groups' => null,
                    'enabled'      => null,
                    'completeness' => null,
                    'document_type' => 'product_model',
                    'technical_id' => 78,
                    'search_id' => 'product_model_78',
                    'complete_variant_product' => [
                        'complete' => 3,
                        'total' => 12,
                    ],
                    'is_checked' => false,
                    'parent' => null,
                ];
        $this->assertSame($data, $this->sut->normalize($productModel, 'datagrid', $context));
    }

    public function test_it_normalizes_a_product_model_without_label(): void
    {
        $productModel = $this->createMock(ProductModelInterface::class);
        $familyVariant = $this->createMock(FamilyVariantInterface::class);
        $family = $this->createMock(FamilyInterface::class);
        $familyEN = $this->createMock(FamilyTranslationInterface::class);
        $values = $this->createMock(WriteValueCollection::class);
        $localeEN = $this->createMock(LocaleInterface::class);
        $channelEcommerce = $this->createMock(ChannelInterface::class);
        $image = $this->createMock(ValueInterface::class);
        $completeness = $this->createMock(CompleteVariantProducts::class);

        $context = [
                    'filter_types' => ['pim.transform.product_value.structured'],
                    'locales'      => ['en_US'],
                    'channels'     => ['ecommerce'],
                    'data_locale'  => 'en_US',
                    'data_channel' => 'ecommerce',
                ];
        $productModel->method('getParent')->willReturn(null);
        $this->findVariantProductCompletenessQuery->method('findComplete')->with($productModel)->willReturn($completeness);
        $completeness->method('value')->with('ecommerce', 'en_US')->willReturn([
                    'complete' => 3,
                    'total' => 12,
                ]);
        $this->filter->method('filterCollection')->with($values, 'pim.transform.product_value.structured', $context)->willReturn($values);
        $productModel->method('getId')->willReturn(78);
        $productModel->method('getFamilyVariant')->willReturn($familyVariant);
        $familyVariant->method('getFamily')->willReturn($family);
        $family->method('getCode')->willReturn('tshirt');
        $family->method('getTranslation')->with('en_US')->willReturn($familyEN);
        $familyEN->method('getLabel')->willReturn(null);
        $productModel->method('getCode')->willReturn('purple_tshirt');
        $productModel->method('getValues')->willReturn($values);
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
        $productModel->method('getCreated')->willReturn($created);
        $updated = new \DateTime('2017-01-01T01:04:34+01:00');
        $productModel->method('getUpdated')->willReturn($updated);
        $productModel->method('getLabel')->with('en_US', 'ecommerce')->willReturn('Purple tshirt');
        $this->imageAsLabel->method('value')->with($productModel)->willReturn($image);
        $this->imageNormalizer->method('normalize')->with($image, 'en_US', 'ecommerce')->willReturn([
                    'filePath'         => '/p/i/m/4/all.png',
                    'originalFileName' => 'all.png',
                ]);
        $localeEN->method('getCode')->willReturn('en_US');
        $channelEcommerce->method('getCode')->willReturn('ecommerce');
        $data = [
                    'identifier'   => 'purple_tshirt',
                    'family'       => '[tshirt]',
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
                    'groups'       => null,
                    'enabled'      => null,
                    'completeness' => null,
                    'document_type' => 'product_model',
                    'technical_id' => 78,
                    'search_id' => 'product_model_78',
                    'complete_variant_product' => [
                        'complete' => 3,
                        'total' => 12,
                    ],
                    'is_checked' => false,
                    'parent' => null,
                ];
        $this->assertSame($data, $this->sut->normalize($productModel, 'datagrid', $context));
    }

    public function test_it_normalizes_a_product_model_with_parent_code(): void
    {
        $productModel = $this->createMock(ProductModelInterface::class);
        $parent = $this->createMock(ProductModelInterface::class);
        $familyVariant = $this->createMock(FamilyVariantInterface::class);
        $family = $this->createMock(FamilyInterface::class);
        $familyEN = $this->createMock(FamilyTranslationInterface::class);
        $values = $this->createMock(WriteValueCollection::class);
        $localeEN = $this->createMock(LocaleInterface::class);
        $channelEcommerce = $this->createMock(ChannelInterface::class);
        $image = $this->createMock(ValueInterface::class);
        $completeness = $this->createMock(CompleteVariantProducts::class);

        $context = [
                    'filter_types' => ['pim.transform.product_value.structured'],
                    'locales'      => ['en_US'],
                    'channels'     => ['ecommerce'],
                    'data_locale'  => 'en_US',
                    'data_channel' => 'ecommerce',
                ];
        $productModel->method('getParent')->willReturn($parent);
        $parent->method('getCode')->willReturn('parent_code');
        $this->findVariantProductCompletenessQuery->method('findComplete')->with($productModel)->willReturn($completeness);
        $completeness->method('value')->with('ecommerce', 'en_US')->willReturn([
                    'complete' => 3,
                    'total' => 12,
                ]);
        $this->filter->method('filterCollection')->with($values, 'pim.transform.product_value.structured', $context)->willReturn($values);
        $productModel->method('getId')->willReturn(78);
        $productModel->method('getFamilyVariant')->willReturn($familyVariant);
        $familyVariant->method('getFamily')->willReturn($family);
        $family->method('getCode')->willReturn('tshirt');
        $family->method('getTranslation')->with('en_US')->willReturn($familyEN);
        $familyEN->method('getLabel')->willReturn(null);
        $productModel->method('getCode')->willReturn('purple_tshirt');
        $productModel->method('getValues')->willReturn($values);
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
        $productModel->method('getCreated')->willReturn($created);
        $updated = new \DateTime('2017-01-01T01:04:34+01:00');
        $productModel->method('getUpdated')->willReturn($updated);
        $productModel->method('getLabel')->with('en_US', 'ecommerce')->willReturn('Purple tshirt');
        $this->imageAsLabel->method('value')->with($productModel)->willReturn($image);
        $this->imageNormalizer->method('normalize')->with($image, 'en_US', 'ecommerce')->willReturn([
                    'filePath'         => '/p/i/m/4/all.png',
                    'originalFileName' => 'all.png',
                ]);
        $localeEN->method('getCode')->willReturn('en_US');
        $channelEcommerce->method('getCode')->willReturn('ecommerce');
        $data = [
                    'identifier'   => 'purple_tshirt',
                    'family'       => '[tshirt]',
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
                    'groups'       => null,
                    'enabled'      => null,
                    'completeness' => null,
                    'document_type' => 'product_model',
                    'technical_id' => 78,
                    'search_id' => 'product_model_78',
                    'complete_variant_product' => [
                        'complete' => 3,
                        'total' => 12,
                    ],
                    'is_checked' => false,
                    'parent' => 'parent_code',
                ];
        $this->assertSame($data, $this->sut->normalize($productModel, 'datagrid', $context));
    }
}
