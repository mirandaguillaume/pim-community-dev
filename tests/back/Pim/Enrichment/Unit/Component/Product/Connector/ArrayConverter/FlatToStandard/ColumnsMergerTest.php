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

    private function stubExtractor(array $map): void
    {
        $this->fieldExtractor->method('extractColumnInfo')
            ->willReturnCallback(fn (string $col) => $map[$col] ?? null);
    }

    public function test_it_does_not_merge_columns_which_does_not_represents_attribute_value(): void
    {
        $row = ['enabled' => '1', 'categories' => 'tshirt,men'];
        $this->stubExtractor(['enabled' => null, 'categories' => null]);
        $this->associationColumnResolver->method('resolveQuantifiedQuantityAssociationColumns')->willReturn([]);
        $this->associationColumnResolver->method('resolveQuantifiedIdentifierAssociationColumns')->willReturn([]);
        $this->assertSame($row, $this->sut->merge($row, []));
    }

    public function test_it_does_not_merge_columns_which_represents_text_attribute_value(): void
    {
        $name = $this->createMock(AttributeInterface::class);
        $row = ['name-fr_FR' => 'T-shirt super beau'];
        $this->stubExtractor(['name-fr_FR' => [
            'attribute' => $name, 'locale_code' => 'fr_FR', 'scope_code' => null, 'metric_unit' => null,
        ]]);
        $name->method('getBackendType')->willReturn('text');
        $this->assertSame($row, $this->sut->merge($row, []));
    }

    public function test_it_does_not_merge_columns_which_represents_metric_attribute_value_in_a_single_column(): void
    {
        $weight = $this->createMock(AttributeInterface::class);
        $row = ['weight' => '10 KILOGRAM'];
        $this->stubExtractor(['weight' => [
            'attribute' => $weight, 'locale_code' => null, 'scope_code' => null, 'metric_unit' => null,
        ]]);
        $weight->method('getCode')->willReturn('weight');
        $weight->method('getBackendType')->willReturn('metric');
        $this->assertSame($row, $this->sut->merge($row, []));
    }

    public function test_it_does_not_merge_columns_which_represents_a_localizable_metric_attribute_value_in_a_single_column(): void
    {
        $weight = $this->createMock(AttributeInterface::class);
        $row = ['weight-fr_FR' => '10 KILOGRAM'];
        $this->stubExtractor(['weight-fr_FR' => [
            'attribute' => $weight, 'locale_code' => 'fr_FR', 'scope_code' => null, 'metric_unit' => null,
        ]]);
        $weight->method('getCode')->willReturn('weight');
        $weight->method('getBackendType')->willReturn('metric');
        $this->assertSame($row, $this->sut->merge($row, []));
    }

    public function test_it_merges_price_attribute_value_columns(): void
    {
        $price = $this->createMock(AttributeInterface::class);
        $row = ['price-EUR' => '10', 'price-USD' => '', 'price-CHF' => '14'];
        $this->stubExtractor([
            'price-EUR' => ['attribute' => $price, 'locale_code' => null, 'scope_code' => null, 'price_currency' => 'EUR'],
            'price-USD' => ['attribute' => $price, 'locale_code' => null, 'scope_code' => null, 'price_currency' => 'USD'],
            'price-CHF' => ['attribute' => $price, 'locale_code' => null, 'scope_code' => null, 'price_currency' => 'CHF'],
        ]);
        $price->method('getCode')->willReturn('price');
        $price->method('getBackendType')->willReturn('prices');
        $this->assertSame(['price' => '10 EUR,14 CHF'], $this->sut->merge($row, []));
    }

    public function test_it_merges_columns_which_represents_metric_attribute_value_in_two_columns(): void
    {
        $weight = $this->createMock(AttributeInterface::class);
        $row = ['weight' => '10', 'weight-unit' => 'KILOGRAM'];
        $this->stubExtractor([
            'weight' => ['attribute' => $weight, 'locale_code' => null, 'scope_code' => null, 'metric_unit' => null],
            'weight-unit' => ['attribute' => $weight, 'locale_code' => null, 'scope_code' => null, 'metric_unit' => 'unit'],
        ]);
        $weight->method('getCode')->willReturn('weight');
        $weight->method('getBackendType')->willReturn('metric');
        $this->assertSame(['weight' => '10 KILOGRAM'], $this->sut->merge($row, []));
    }

    public function test_it_merges_columns_which_represents_metric_attribute_value_with_scientific_notation_in_two_columns(): void
    {
        $weight = $this->createMock(AttributeInterface::class);
        $row = ['weight' => 0.000075, 'weight-unit' => 'GRAM'];
        $this->stubExtractor([
            'weight' => ['attribute' => $weight, 'locale_code' => null, 'scope_code' => null, 'metric_unit' => null],
            'weight-unit' => ['attribute' => $weight, 'locale_code' => null, 'scope_code' => null, 'metric_unit' => 'unit'],
        ]);
        $weight->method('getCode')->willReturn('weight');
        $weight->method('getBackendType')->willReturn('metric');
        $this->assertSame(['weight' => '0.000075000000 GRAM'], $this->sut->merge($row, []));
    }

    public function test_it_merges_columns_which_represents_metric_attribute_value_with_large_decimal_number(): void
    {
        $weight = $this->createMock(AttributeInterface::class);
        $row = ['weight' => 80000.75, 'weight-unit' => 'GRAM'];
        $this->stubExtractor([
            'weight' => ['attribute' => $weight, 'locale_code' => null, 'scope_code' => null, 'metric_unit' => null],
            'weight-unit' => ['attribute' => $weight, 'locale_code' => null, 'scope_code' => null, 'metric_unit' => 'unit'],
        ]);
        $weight->method('getCode')->willReturn('weight');
        $weight->method('getBackendType')->willReturn('metric');
        $this->assertSame(['weight' => '80000.750000000000 GRAM'], $this->sut->merge($row, []));
    }

    public function test_it_merges_columns_which_represents_metric_attribute_value_with_the_decimal_separator(): void
    {
        $weight = $this->createMock(AttributeInterface::class);
        $row = ['weight' => 80000.75, 'weight-unit' => 'GRAM'];
        $this->stubExtractor([
            'weight' => ['attribute' => $weight, 'locale_code' => null, 'scope_code' => null, 'metric_unit' => null],
            'weight-unit' => ['attribute' => $weight, 'locale_code' => null, 'scope_code' => null, 'metric_unit' => 'unit'],
        ]);
        $weight->method('getCode')->willReturn('weight');
        $weight->method('getBackendType')->willReturn('metric');
        $this->assertSame(['weight' => '80000,750000000000 GRAM'], $this->sut->merge($row, ['decimal_separator' => ',']));
    }

    public function test_it_merges_columns_which_represents_a_localizable_metric_attribute_value_in_a_two_columns(): void
    {
        $weight = $this->createMock(AttributeInterface::class);
        $row = ['weight-fr_FR' => '10', 'weight-fr_FR-unit' => 'KILOGRAM'];
        $this->stubExtractor([
            'weight-fr_FR' => ['attribute' => $weight, 'locale_code' => 'fr_FR', 'scope_code' => null, 'metric_unit' => null],
            'weight-fr_FR-unit' => ['attribute' => $weight, 'locale_code' => 'fr_FR', 'scope_code' => null, 'metric_unit' => 'unit'],
        ]);
        $weight->method('getCode')->willReturn('weight');
        $weight->method('getBackendType')->willReturn('metric');
        $this->assertSame(['weight-fr_FR' => '10 KILOGRAM'], $this->sut->merge($row, []));
    }

    public function test_it_does_not_merge_columns_which_represents_a_localizable_price_attribute_value_in_a_single_column(): void
    {
        $price = $this->createMock(AttributeInterface::class);
        $row = ['price-fr_FR' => '10 EUR, 24 USD'];
        $this->stubExtractor(['price-fr_FR' => [
            'attribute' => $price, 'locale_code' => 'fr_FR', 'scope_code' => null, 'price_currency' => null,
        ]]);
        $price->method('getCode')->willReturn('price');
        $price->method('getBackendType')->willReturn('prices');
        $this->assertSame($row, $this->sut->merge($row, []));
    }

    public function test_it_does_not_create_price_when_price_is_empty(): void
    {
        $price = $this->createMock(AttributeInterface::class);
        $row = ['price-EUR' => ''];
        $this->stubExtractor(['price-EUR' => [
            'attribute' => $price, 'locale_code' => null, 'scope_code' => null, 'price_currency' => 'EUR',
        ]]);
        $price->method('getCode')->willReturn('price');
        $price->method('getBackendType')->willReturn('prices');
        $this->assertSame(['price' => ''], $this->sut->merge($row, []));
    }

    public function test_it_merges_columns_which_represents_price_attribute_value_in_many_columns(): void
    {
        $price = $this->createMock(AttributeInterface::class);
        $row = ['price-EUR' => '10', 'price-USD' => 12, 'price-CHF' => '14', 'price-ARS' => 12.23];
        $this->stubExtractor([
            'price-EUR' => ['attribute' => $price, 'locale_code' => null, 'scope_code' => null, 'price_currency' => 'EUR'],
            'price-USD' => ['attribute' => $price, 'locale_code' => null, 'scope_code' => null, 'price_currency' => 'USD'],
            'price-CHF' => ['attribute' => $price, 'locale_code' => null, 'scope_code' => null, 'price_currency' => 'CHF'],
            'price-ARS' => ['attribute' => $price, 'locale_code' => null, 'scope_code' => null, 'price_currency' => 'ARS'],
        ]);
        $price->method('getCode')->willReturn('price');
        $price->method('getBackendType')->willReturn('prices');
        $this->assertSame(['price' => '10 EUR,12 USD,14 CHF,12.23 ARS'], $this->sut->merge($row, []));
    }

    public function test_it_merges_columns_which_represents_price_attribute_with_decimal_separator(): void
    {
        $price = $this->createMock(AttributeInterface::class);
        $row = ['price-EUR' => 10.63];
        $this->stubExtractor(['price-EUR' => [
            'attribute' => $price, 'locale_code' => null, 'scope_code' => null, 'price_currency' => 'EUR',
        ]]);
        $price->method('getCode')->willReturn('price');
        $price->method('getBackendType')->willReturn('prices');
        $this->assertSame(['price' => '10,63 EUR'], $this->sut->merge($row, ['decimal_separator' => ',']));
    }

    public function test_it_throws_an_exception_when_an_attribute_price_value_is_a_datetime(): void
    {
        $price = $this->createMock(AttributeInterface::class);
        $row = ['price-USD' => new \DateTimeImmutable('2021-11-22')];
        $this->stubExtractor(['price-USD' => [
            'attribute' => $price, 'locale_code' => null, 'scope_code' => null, 'price_currency' => 'USD',
        ]]);
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
        $this->stubExtractor(['PACK-products-quantity' => null, 'PACK-products' => null]);
        $this->associationColumnResolver->method('resolveQuantifiedQuantityAssociationColumns')->willReturn(['PACK-products-quantity']);
        $this->associationColumnResolver->method('resolveQuantifiedIdentifierAssociationColumns')->willReturn(['PACK-products']);
        $expected = [
            'PACK-products' => [
                ['uuid' => 'd8ddf845-9dad-46dd-ad38-5eea5c1b179d', 'quantity' => 10],
                ['uuid' => '3b2571c2-4997-455f-afe0-9abb71b8185c', 'quantity' => 24],
            ],
            'PACK-product_models' => [],
        ];
        $this->assertSame($expected, $this->sut->merge($row, []));
    }

    public function test_it_merges_columns_which_represents_quantified_associations_in_two_columns_with_identifiers(): void
    {
        $row = ['PACK-products-quantity' => '10|24', 'PACK-products' => 'my_sku,nice'];
        $this->stubExtractor(['PACK-products-quantity' => null, 'PACK-products' => null]);
        $this->associationColumnResolver->method('resolveQuantifiedQuantityAssociationColumns')->willReturn(['PACK-products-quantity']);
        $this->associationColumnResolver->method('resolveQuantifiedIdentifierAssociationColumns')->willReturn(['PACK-products']);
        $expected = [
            'PACK-products' => [
                ['identifier' => 'my_sku', 'quantity' => 10],
                ['identifier' => 'nice', 'quantity' => 24],
            ],
            'PACK-product_models' => [],
        ];
        $this->assertSame($expected, $this->sut->merge($row, []));
    }

    public function test_it_removes_line_breaks_from_measurements(): void
    {
        $weight = $this->createMock(AttributeInterface::class);
        $row = ['weight' => "10\n", "weight-unit" => "CENTIMETER"];
        $this->stubExtractor([
            'weight' => ['attribute' => $weight, 'locale_code' => null, 'scope_code' => null, 'metric_unit' => null],
            'weight-unit' => ['attribute' => $weight, 'locale_code' => null, 'scope_code' => null, 'metric_unit' => 'unit'],
        ]);
        $weight->method('getBackendType')->willReturn('metric');
        $weight->method('getCode')->willReturn('weight');
        $this->associationColumnResolver->method('resolveQuantifiedQuantityAssociationColumns')->willReturn([]);
        $this->associationColumnResolver->method('resolveQuantifiedIdentifierAssociationColumns')->willReturn([]);
        $this->assertSame(['weight' => "10 CENTIMETER"], $this->sut->merge($row, []));
    }

    public function test_it_throw_an_exception_on_missing_column_for_quantified_association(): void
    {
        $row = ['PACK-products' => 'my_sku,nice'];
        $this->stubExtractor(['PACK-products-quantity' => null, 'PACK-products' => null]);
        $this->associationColumnResolver->method('resolveQuantifiedQuantityAssociationColumns')->willReturn(['PACK-products-quantity']);
        $this->associationColumnResolver->method('resolveQuantifiedIdentifierAssociationColumns')->willReturn(['PACK-products']);
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('A "PACK-products-quantity" column is missing for quantified association');
        $this->sut->merge($row, []);
    }
}
