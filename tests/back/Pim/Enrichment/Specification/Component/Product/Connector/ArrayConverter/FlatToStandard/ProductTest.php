<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ColumnsMapper;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ColumnsMerger;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ConvertedField;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldConverter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\Product;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScoresInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Exception\StructureArrayConversionException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    private Product $sut;

    protected function setUp(): void
    {
        $this->sut = new Product();
    }

    public function test_it_converts_product_with_quality_scores(): void
    {
        $enable = $this->createMock(ConvertedField::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $qualityScoreField = sprintf('%s-ecommerce-en_US', GetProductsWithQualityScoresInterface::FLAT_FIELD_PREFIX);
        $product = ['sku' => '1069978', 'enabled' => true, $qualityScoreField => 'B'];
        $filteredProduct = ['sku' => '1069978', 'enabled' => true];
        $attrColumnsResolver->resolveAttributeColumns()->willReturn(['sku']);
        $assocColumnsResolver->resolveAssociationColumns()->willReturn([]);
        $assocColumnsResolver->resolveQuantifiedAssociationColumns()->willReturn([]);
        $columnsMerger->merge($filteredProduct, ['with_associations' => false, 'default_values' => []])->willReturn($filteredProduct);
        $attrColumnsResolver->resolveIdentifierField()->willReturn('sku');
        $attribute->method('getType')->willReturn('sku');
        $fieldConverter->supportsColumn('sku')->willReturn(false);
        $fieldConverter->supportsColumn('enabled')->willReturn(true);
        $fieldConverter->convert('enabled', true)->willReturn($enable);
        $enable->method('appendTo')->with([])->willReturn([
                        'enabled' => true
                    ]);
        $attributeRepository->getIdentifierCode()->willReturn('sku');
        $productValueConverter->convert(['sku' => '1069978'])->willReturn(
                    [
                        'sku' => [
                            [
                                'locale' => '',
                                'scope'  => '',
                                'data'   => 1069978
                            ]
                        ]
                    ]
                );
        $convertedProduct = [
                    'enabled'    => true,
                    'values'     => [
                        'sku' => [
                            [
                                'locale' => '',
                                'scope'  => '',
                                'data'   => 1069978,
                            ]
                        ],
                    ],
                    'identifier' => 1069978,
                ];
        $this->assertSame($convertedProduct, $this->sut->convert($product, ['with_associations' => false]));
    }
}
