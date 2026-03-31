<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ColumnsMerger;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\Connector\Exception\BusinessArrayConversionException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ColumnsMergerTest extends TestCase
{
    private AttributeColumnInfoExtractor|MockObject $fieldExtractor;
    private AssociationColumnsResolver|MockObject $associationColumnResolver;
    private ColumnsMerger $sut;

    protected function setUp(): void
    {
        $this->fieldExtractor = $this->createMock(AttributeColumnInfoExtractor::class);
        $this->associationColumnResolver = $this->createMock(AssociationColumnsResolver::class);
        $this->sut = new ColumnsMerger($this->fieldExtractor, $this->associationColumnResolver);
    }

    public function test_it_does_not_merge_columns_which_does_not_represents_attribute_value(): void
    {
        $row = [
                    'enabled' => '1',
                    'categories' => 'tshirt,men',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('enabled')->willReturn(null);
        $this->fieldExtractor->method('extractColumnInfo')->with('categories')->willReturn(null);
        $this->associationColumnResolver->method('resolveQuantifiedQuantityAssociationColumns')->willReturn([]);
        $this->associationColumnResolver->method('resolveQuantifiedIdentifierAssociationColumns')->willReturn([]);
        $mergedRow = $row;
        $this->assertSame($mergedRow, $this->sut->merge($row, []));
    }

    public function test_it_does_not_merge_columns_which_represents_text_attribute_value(): void
    {
        $name = $this->createMock(AttributeInterface::class);

        $row = [
                    'name-fr_FR' => 'T-shirt super beau',
                ];
        $attributeInfoData = [
                    'attribute' => $name,
                    'locale_code' => 'fr_FR',
                    'scope_code' => null,
                    'metric_unit' => null,
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('name-fr_FR')->willReturn($attributeInfoData);
        $name->method('getBackendType')->willReturn('text');
        $mergedRow = $row;
        $this->assertSame($mergedRow, $this->sut->merge($row, []));
    }

    public function test_it_does_not_merge_columns_which_represents_metric_attribute_value_in_a_single_column(): void
    {
        $weight = $this->createMock(AttributeInterface::class);

        $row = [
                    'weight' => '10 KILOGRAM',
                ];
        $attributeInfoData = [
                    'attribute' => $weight,
                    'locale_code' => null,
                    'scope_code' => null,
                    'metric_unit' => null,
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('weight')->willReturn($attributeInfoData);
        $weight->method('getCode')->willReturn('weight');
        $weight->method('getBackendType')->willReturn('metric');
        $mergedRow = $row;
        $this->assertSame($mergedRow, $this->sut->merge($row, []));
    }

    public function test_it_does_not_merge_columns_which_represents_a_localizable_metric_attribute_value_in_a_single_column(): void
    {
        $weight = $this->createMock(AttributeInterface::class);

        $row = [
                    'weight-fr_FR' => '10 KILOGRAM',
                ];
        $attributeInfoData = [
                    'attribute' => $weight,
                    'locale_code' => 'fr_FR',
                    'scope_code' => null,
                    'metric_unit' => null,
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('weight-fr_FR')->willReturn($attributeInfoData);
        $weight->method('getCode')->willReturn('weight');
        $weight->method('getBackendType')->willReturn('metric');
        $mergedRow = $row;
        $this->assertSame($mergedRow, $this->sut->merge($row, []));
    }

    public function test_it_merges_price_attribute_value_columns(): void
    {
        $price = $this->createMock(AttributeInterface::class);

        $row = [
                    'price-EUR' => '10',
                    'price-USD' => '',
                    'price-CHF' => '14',
                ];
        $attributeInfoEur = [
                    'attribute' => $price,
                    'locale_code' => null,
                    'scope_code' => null,
                    'price_currency' => 'EUR',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('price-EUR')->willReturn($attributeInfoEur);
        $attributeInfoUsd = [
                    'attribute' => $price,
                    'locale_code' => null,
                    'scope_code' => null,
                    'price_currency' => 'USD',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('price-USD')->willReturn($attributeInfoUsd);
        $attributeInfoChf = [
                    'attribute' => $price,
                    'locale_code' => null,
                    'scope_code' => null,
                    'price_currency' => 'CHF',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('price-CHF')->willReturn($attributeInfoChf);
        $price->method('getCode')->willReturn('price');
        $price->method('getBackendType')->willReturn('prices');
        $mergedRow = ['price' => '10 EUR,14 CHF'];
        $this->assertSame($mergedRow, $this->sut->merge($row, []));
    }

    public function test_it_merges_columns_which_represents_metric_attribute_value_in_two_columns(): void
    {
        $weight = $this->createMock(AttributeInterface::class);

        $row = [
                    'weight' => '10',
                    'weight-unit' => 'KILOGRAM',
                ];
        $attributeInfoData = [
                    'attribute' => $weight,
                    'locale_code' => null,
                    'scope_code' => null,
                    'metric_unit' => null,
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('weight')->willReturn($attributeInfoData);
        $attributeInfoUnit = [
                    'attribute' => $weight,
                    'locale_code' => null,
                    'scope_code' => null,
                    'metric_unit' => 'unit',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('weight-unit')->willReturn($attributeInfoUnit);
        $weight->method('getCode')->willReturn('weight');
        $weight->method('getBackendType')->willReturn('metric');
        $mergedRow = ['weight' => '10 KILOGRAM'];
        $this->assertSame($mergedRow, $this->sut->merge($row, []));
    }

    public function test_it_merges_columns_which_represents_metric_attribute_value_with_scientific_notation_in_two_columns(): void
    {
        $weight = $this->createMock(AttributeInterface::class);

        $row = [
                    'weight' => 0.000075,
                    'weight-unit' => 'GRAM',
                ];
        $attributeInfoData = [
                    'attribute' => $weight,
                    'locale_code' => null,
                    'scope_code' => null,
                    'metric_unit' => null,
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('weight')->willReturn($attributeInfoData);
        $attributeInfoUnit = [
                    'attribute' => $weight,
                    'locale_code' => null,
                    'scope_code' => null,
                    'metric_unit' => 'unit',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('weight-unit')->willReturn($attributeInfoUnit);
        $weight->method('getCode')->willReturn('weight');
        $weight->method('getBackendType')->willReturn('metric');
        $mergedRow = ['weight' => '0.000075000000 GRAM'];
        $this->assertSame($mergedRow, $this->sut->merge($row, []));
    }

    public function test_it_merges_columns_which_represents_metric_attribute_value_with_large_decimal_number(): void
    {
        $weight = $this->createMock(AttributeInterface::class);

        $row = [
                    'weight' => 80000.75,
                    'weight-unit' => 'GRAM',
                ];
        $attributeInfoData = [
                    'attribute' => $weight,
                    'locale_code' => null,
                    'scope_code' => null,
                    'metric_unit' => null,
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('weight')->willReturn($attributeInfoData);
        $attributeInfoUnit = [
                    'attribute' => $weight,
                    'locale_code' => null,
                    'scope_code' => null,
                    'metric_unit' => 'unit',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('weight-unit')->willReturn($attributeInfoUnit);
        $weight->method('getCode')->willReturn('weight');
        $weight->method('getBackendType')->willReturn('metric');
        $mergedRow = ['weight' => '80000.750000000000 GRAM'];
        $this->assertSame($mergedRow, $this->sut->merge($row, []));
    }

    public function test_it_merges_columns_which_represents_metric_attribute_value_with_the_decimal_separator(): void
    {
        $weight = $this->createMock(AttributeInterface::class);

        $row = [
                    'weight' => 80000.75,
                    'weight-unit' => 'GRAM',
                ];
        $attributeInfoData = [
                    'attribute' => $weight,
                    'locale_code' => null,
                    'scope_code' => null,
                    'metric_unit' => null,
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('weight')->willReturn($attributeInfoData);
        $attributeInfoUnit = [
                    'attribute' => $weight,
                    'locale_code' => null,
                    'scope_code' => null,
                    'metric_unit' => 'unit',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('weight-unit')->willReturn($attributeInfoUnit);
        $weight->method('getCode')->willReturn('weight');
        $weight->method('getBackendType')->willReturn('metric');
        $mergedRow = ['weight' => '80000,750000000000 GRAM'];
        $this->assertSame($mergedRow, $this->sut->merge($row, ['decimal_separator' => ',']));
    }

    public function test_it_merges_columns_which_represents_a_localizable_metric_attribute_value_in_a_two_columns(): void
    {
        $weight = $this->createMock(AttributeInterface::class);

        $row = [
                    'weight-fr_FR' => '10',
                    'weight-fr_FR-unit' => 'KILOGRAM',
                ];
        $attributeInfoData = [
                    'attribute' => $weight,
                    'locale_code' => 'fr_FR',
                    'scope_code' => null,
                    'metric_unit' => null,
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('weight-fr_FR')->willReturn($attributeInfoData);
        $attributeInfoUnit = [
                    'attribute' => $weight,
                    'locale_code' => 'fr_FR',
                    'scope_code' => null,
                    'metric_unit' => 'unit',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('weight-fr_FR-unit')->willReturn($attributeInfoUnit);
        $weight->method('getCode')->willReturn('weight');
        $weight->method('getBackendType')->willReturn('metric');
        $mergedRow = ['weight-fr_FR' => '10 KILOGRAM'];
        $this->assertSame($mergedRow, $this->sut->merge($row, []));
    }

    public function test_it_does_not_merge_columns_which_represents_a_localizable_price_attribute_value_in_a_single_column(): void
    {
        $price = $this->createMock(AttributeInterface::class);

        $row = [
                    'price-fr_FR' => '10 EUR, 24 USD',
                ];
        $attributeInfoData = [
                    'attribute' => $price,
                    'locale_code' => 'fr_FR',
                    'scope_code' => null,
                    'price_currency' => null,
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('price-fr_FR')->willReturn($attributeInfoData);
        $price->method('getCode')->willReturn('price');
        $price->method('getBackendType')->willReturn('prices');
        $mergedRow = $row;
        $this->assertSame($mergedRow, $this->sut->merge($row, []));
    }

    public function test_it_does_not_create_price_when_price_is_empty(): void
    {
        $price = $this->createMock(AttributeInterface::class);

        $row = ['price-EUR' => ''];
        $attributeInfoEur = [
                    'attribute' => $price,
                    'locale_code' => null,
                    'scope_code' => null,
                    'price_currency' => 'EUR',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('price-EUR')->willReturn($attributeInfoEur);
        $price->method('getCode')->willReturn('price');
        $price->method('getBackendType')->willReturn('prices');
        $this->assertSame(['price' => ''], $this->sut->merge($row, []));
    }

    public function test_it_merges_columns_which_represents_price_attribute_value_in_many_columns(): void
    {
        $price = $this->createMock(AttributeInterface::class);

        $row = [
                    'price-EUR' => '10',
                    'price-USD' => 12,
                    'price-CHF' => '14',
                    'price-ARS' => 12.23,
                ];
        $attributeInfoEur = [
                    'attribute' => $price,
                    'locale_code' => null,
                    'scope_code' => null,
                    'price_currency' => 'EUR',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('price-EUR')->willReturn($attributeInfoEur);
        $attributeInfoUsd = [
                    'attribute' => $price,
                    'locale_code' => null,
                    'scope_code' => null,
                    'price_currency' => 'USD',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('price-USD')->willReturn($attributeInfoUsd);
        $attributeInfoChf = [
                    'attribute' => $price,
                    'locale_code' => null,
                    'scope_code' => null,
                    'price_currency' => 'CHF',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('price-CHF')->willReturn($attributeInfoChf);
        $attributeInfoArs = [
                    'attribute' => $price,
                    'locale_code' => null,
                    'scope_code' => null,
                    'price_currency' => 'ARS',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('price-ARS')->willReturn($attributeInfoArs);
        $price->method('getCode')->willReturn('price');
        $price->method('getBackendType')->willReturn('prices');
        $mergedRow = ['price' => '10 EUR,12 USD,14 CHF,12.23 ARS'];
        $this->assertSame($mergedRow, $this->sut->merge($row, []));
    }

    public function test_it_merges_columns_which_represents_price_attribute_with_decimal_separator(): void
    {
        $price = $this->createMock(AttributeInterface::class);

        $row = [
                    'price-EUR' => 10.63,
                ];
        $attributeInfoEur = [
                    'attribute' => $price,
                    'locale_code' => null,
                    'scope_code' => null,
                    'price_currency' => 'EUR',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('price-EUR')->willReturn($attributeInfoEur);
        $price->method('getCode')->willReturn('price');
        $price->method('getBackendType')->willReturn('prices');
        $mergedRow = ['price' => '10,63 EUR'];
        $this->assertSame($mergedRow, $this->sut->merge($row, ['decimal_separator' => ',']));
    }

    public function test_it_throws_an_exception_when_an_attribute_price_value_is_a_datetime(): void
    {
        $price = $this->createMock(AttributeInterface::class);

        $row = ['price-USD' => new \DateTimeImmutable('2021-11-22')];
        $attributeInfoUsd = [
                    'attribute' => $price,
                    'locale_code' => null,
                    'scope_code' => null,
                    'price_currency' => 'USD',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('price-USD')->willReturn($attributeInfoUsd);
        $price->method('getCode')->willReturn('price');
        $price->method('getBackendType')->willReturn('prices');
        $this->expectException(BusinessArrayConversionException::class);
        $this->sut->merge($row, []);
    }

    public function test_it_merges_columns_which_represents_quantified_associations_in_two_columns_with_uuids(): void
    {
        $row = [
                    'PACK-products-quantity' => '10|24',
                    'PACK-products' => 'd8ddf845-9dad-46dd-ad38-5eea5c1b179d,3b2571c2-4997-455f-afe0-9abb71b8185c',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('PACK-products-quantity')->willReturn(null);
        $this->fieldExtractor->method('extractColumnInfo')->with('PACK-products')->willReturn(null);
        $this->associationColumnResolver->method('resolveQuantifiedQuantityAssociationColumns')->willReturn(['PACK-products-quantity']);
        $this->associationColumnResolver->method('resolveQuantifiedIdentifierAssociationColumns')->willReturn(['PACK-products']);
        $mergedRow = [
                    'PACK-products' => [
                        [
                            'uuid' => 'd8ddf845-9dad-46dd-ad38-5eea5c1b179d',
                            'quantity' => 10,
                        ],
                        [
                            'uuid' => '3b2571c2-4997-455f-afe0-9abb71b8185c',
                            'quantity' => 24,
                        ],
                    ],
                    'PACK-product_models' => [],
                ];
        $this->assertSame($mergedRow, $this->sut->merge($row, []));
    }

    public function test_it_merges_columns_which_represents_quantified_associations_in_two_columns_with_identifiers(): void
    {
        $row = [
                    'PACK-products-quantity' => '10|24',
                    'PACK-products' => 'my_sku,nice',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('PACK-products-quantity')->willReturn(null);
        $this->fieldExtractor->method('extractColumnInfo')->with('PACK-products')->willReturn(null);
        $this->associationColumnResolver->method('resolveQuantifiedQuantityAssociationColumns')->willReturn(['PACK-products-quantity']);
        $this->associationColumnResolver->method('resolveQuantifiedIdentifierAssociationColumns')->willReturn(['PACK-products']);
        $mergedRow = [
                    'PACK-products' => [
                        [
                            'identifier' => 'my_sku',
                            'quantity' => 10,
                        ],
                        [
                            'identifier' => 'nice',
                            'quantity' => 24,
                        ],
                    ],
                    'PACK-product_models' => [],
                ];
        $this->assertSame($mergedRow, $this->sut->merge($row, []));
    }

    public function test_it_removes_line_breaks_from_measurements(): void
    {
        $weight = $this->createMock(AttributeInterface::class);

        $row = [
                    'weight' => "10\n",
                    "weight-unit" => "CENTIMETER"
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('weight')->willReturn([
                    'attribute' => $weight,
                    'locale_code' => null,
                    'scope_code' => null,
                    'metric_unit' => null,
                ]);
        $this->fieldExtractor->method('extractColumnInfo')->with('weight-unit')->willReturn([
                    'attribute' => $weight,
                    'locale_code' => null,
                    'scope_code' => null,
                    'metric_unit' => 'unit',
                ]);
        $weight->method('getBackendType')->willReturn('metric');
        $weight->method('getCode')->willReturn('weight');
        $this->associationColumnResolver->method('resolveQuantifiedQuantityAssociationColumns')->willReturn([]);
        $this->associationColumnResolver->method('resolveQuantifiedIdentifierAssociationColumns')->willReturn([]);
        $this->assertSame(['weight' => "10 CENTIMETER"], $this->sut->merge($row, []));
    }

    public function test_it_throw_an_exception_on_missing_column_for_quantified_association(): void
    {
        $row = [
                    'PACK-products' => 'my_sku,nice',
                ];
        $this->fieldExtractor->method('extractColumnInfo')->with('PACK-products-quantity')->willReturn(null);
        $this->fieldExtractor->method('extractColumnInfo')->with('PACK-products')->willReturn(null);
        $this->associationColumnResolver->method('resolveQuantifiedQuantityAssociationColumns')->willReturn(['PACK-products-quantity']);
        $this->associationColumnResolver->method('resolveQuantifiedIdentifierAssociationColumns')->willReturn(['PACK-products']);
        $this->expectException(\LogicException::class);

        $this->expectExceptionMessage('A "PACK-products-quantity" column is missing for quantified association');
        $this->sut->merge($row, []);
    }
}
