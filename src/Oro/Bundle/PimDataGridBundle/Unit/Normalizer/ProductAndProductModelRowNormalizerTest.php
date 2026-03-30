<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Oro\Bundle\PimDataGridBundle\Normalizer\ProductAndProductModelRowNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductAndProductModelRowNormalizerTest extends TestCase
{
    private NormalizerInterface|MockObject $normalizer;
    private ImageNormalizer|MockObject $imageNormalizer;
    private ProductAndProductModelRowNormalizer $sut;

    protected function setUp(): void
    {
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->imageNormalizer = $this->createMock(ImageNormalizer::class);
        $this->sut = new ProductAndProductModelRowNormalizer($this->imageNormalizer);
        $this->normalizer->method('implement')->with(NormalizerInterface::class);
        $this->sut->setNormalizer($this->normalizer);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductAndProductModelRowNormalizer::class, $this->sut);
        $this->assertInstanceOf(NormalizerAwareInterface::class, $this->sut);
    }

    public function test_it_is_a_normalizer(): void
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->sut);
    }

    public function test_it_supports_datagrid_format_and_row(): void
    {
        $row = Row::fromProduct(
            'identifier',
            'family label',
            ['group_1', 'group_2'],
            true,
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            'label',
            null,
            90,
            '54162e35-ff81-48f1-96d5-5febd3f00fd5',
            'parent_code',
            new WriteValueCollection([])
        );
        $this->assertSame(true, $this->sut->supportsNormalization($row, 'datagrid'));
        $this->assertSame(false, $this->sut->supportsNormalization($row, 'other_format'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'other_format'));
        $this->assertSame(false, $this->sut->supportsNormalization(new \stdClass(), 'datagrid'));
    }

    public function test_it_normalizes_a_row(): void
    {
        $values = new WriteValueCollection([ScalarValue::value('scalar_attribute', 'data')]);
        $row = Row::fromProduct(
            'identifier',
            'family label',
            ['group_1', 'group_2'],
            true,
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            'data',
            MediaValue::value('media_attribute', new FileInfo()),
            90,
            '54162e35-ff81-48f1-96d5-5febd3f00fd5',
            'parent_code',
            $values
        );
        $row = $row->addAdditionalProperty(new AdditionalProperty('name', 'value'));
        $context = [
                    'locales'      => ['en_US'],
                    'channels'     => ['ecommerce'],
                    'data_locale'  => 'en_US',
                    'data_channel' => null,
                ];
        $this->normalizer->method('normalize')->with($values, 'datagrid', $context)->willReturn([
                    'scalar_attribute' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'data',
                        ],
                    ],
                ]);
        $this->normalizer->method('normalize')->with($row->created(), 'datagrid', $context)->willReturn('2018-05-23T15:55:50+01:00');
        $this->normalizer->method('normalize')->with($row->updated(), 'datagrid', $context)->willReturn('2018-05-23T15:55:50+01:00');
        $this->imageNormalizer->method('normalize')->with($row->image(), 'en_US', null)->willReturn([
                    'filePath'         => '/p/i/m/4/all.png',
                    'originalFileName' => 'all.png',
                ]);
        $data = [
                    'identifier'   => 'identifier',
                    'family'       => 'family label',
                    'groups'       => 'group_1,group_2',
                    'enabled'      => true,
                    'values'       => [
                        'scalar_attribute' => [
                            [
                                'locale' => null,
                                'scope'  => null,
                                'data'   => 'data',
                            ],
                        ],
                    ],
                    'created'      => '2018-05-23T15:55:50+01:00',
                    'updated'      => '2018-05-23T15:55:50+01:00',
                    'label'        => 'data',
                    'image'        => [
                        'filePath'         => '/p/i/m/4/all.png',
                        'originalFileName' => 'all.png',
                    ],
                    'completeness' => 90,
                    'document_type' => 'product',
                    'technical_id' => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
                    'id'           => '54162e35-ff81-48f1-96d5-5febd3f00fd5',
                    'search_id' => 'product_54162e35-ff81-48f1-96d5-5febd3f00fd5',
                    'is_checked' => true,
                    'complete_variant_product' => [],
                    'parent' => 'parent_code',
                    'name' => 'value',
                ];
        $this->assertSame($data, $this->sut->normalize($row, 'datagrid', $context));
    }
}
