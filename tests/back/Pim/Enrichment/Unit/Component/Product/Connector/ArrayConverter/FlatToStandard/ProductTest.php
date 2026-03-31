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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    private AssociationColumnsResolver|MockObject $assocColumnsResolver;
    private AttributeColumnsResolver|MockObject $attrColumnsResolver;
    private FieldConverter|MockObject $fieldConverter;
    private ColumnsMerger|MockObject $columnsMerger;
    private ColumnsMapper|MockObject $columnsMapper;
    private AttributeRepositoryInterface|MockObject $attributeRepository;
    private ArrayConverterInterface|MockObject $productValueConverter;
    private Product $sut;

    protected function setUp(): void
    {
        $this->assocColumnsResolver = $this->createMock(AssociationColumnsResolver::class);
        $this->attrColumnsResolver = $this->createMock(AttributeColumnsResolver::class);
        $this->fieldConverter = $this->createMock(FieldConverter::class);
        $this->columnsMerger = $this->createMock(ColumnsMerger::class);
        $this->columnsMapper = $this->createMock(ColumnsMapper::class);
        $this->attributeRepository = $this->createMock(AttributeRepositoryInterface::class);
        $this->productValueConverter = $this->createMock(ArrayConverterInterface::class);
        $this->sut = new Product(
            $this->assocColumnsResolver,
            $this->attrColumnsResolver,
            $this->fieldConverter,
            $this->columnsMerger,
            $this->columnsMapper,
            $this->attributeRepository,
            $this->productValueConverter
        );
    }

    public function test_it_converts_product_with_quality_scores(): void
    {
        $enable = $this->createMock(ConvertedField::class);

        $qualityScoreField = sprintf('%s-ecommerce-en_US', GetProductsWithQualityScoresInterface::FLAT_FIELD_PREFIX);
        $product = ['sku' => '1069978', 'enabled' => true, $qualityScoreField => 'B'];
        $filteredProduct = ['sku' => '1069978', 'enabled' => true];

        $this->attrColumnsResolver->method('resolveAttributeColumns')->willReturn(['sku']);
        $this->assocColumnsResolver->method('resolveAssociationColumns')->willReturn([]);
        $this->assocColumnsResolver->method('resolveQuantifiedAssociationColumns')->willReturn([]);
        $this->columnsMerger->method('merge')->willReturn($filteredProduct);
        $this->columnsMapper->method('map')->willReturnArgument(0);
        $this->attrColumnsResolver->method('resolveIdentifierField')->willReturn('sku');
        $this->fieldConverter->method('supportsColumn')->willReturnCallback(fn (string $col) => $col === 'enabled');
        $this->fieldConverter->method('convert')->with('enabled', true)->willReturn($enable);
        $enable->method('appendTo')->willReturn(['enabled' => true]);
        $this->attributeRepository->method('getIdentifierCode')->willReturn('sku');
        $this->productValueConverter->method('convert')->with(['sku' => '1069978'])->willReturn([
            'sku' => [['locale' => '', 'scope' => '', 'data' => 1069978]],
        ]);

        $convertedProduct = [
            'enabled' => true,
            'values' => [
                'sku' => [['locale' => '', 'scope' => '', 'data' => 1069978]],
            ],
            'identifier' => 1069978,
        ];
        $this->assertSame($convertedProduct, $this->sut->convert($product, ['with_associations' => false]));
    }
}
