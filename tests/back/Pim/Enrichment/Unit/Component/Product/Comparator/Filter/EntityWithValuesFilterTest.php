<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Comparator\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute\MetricComparator;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute\NumberComparator;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Attribute\PricesComparator;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\EntityWithValuesFilter;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * The product edit form save filters the posted values through this filter (pim_catalog.comparator.filter.product)
 * before building the user intents. It is tested here with a real ComparatorRegistry holding the real number, metric
 * and price comparators, so the contract between the filter and the comparators is what gets checked, zero values
 * included (PIM-5666).
 *
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityWithValuesFilterTest extends TestCase
{
    private const ATTRIBUTE_TYPES = [
        'a_number' => 'pim_catalog_number',
        'a_metric' => 'pim_catalog_metric',
        'a_price' => 'pim_catalog_price_collection',
    ];

    private NormalizerInterface|MockObject $normalizer;
    private FilterInterface|MockObject $productFieldFilter;
    private ProductInterface|MockObject $product;
    private EntityWithValuesFilter $sut;

    protected function setUp(): void
    {
        $comparatorRegistry = new ComparatorRegistry();
        $comparatorRegistry->addAttributeComparator(new NumberComparator(['pim_catalog_number']), 100);
        $comparatorRegistry->addAttributeComparator(new MetricComparator(['pim_catalog_metric']), 100);
        $comparatorRegistry->addAttributeComparator(new PricesComparator(['pim_catalog_price_collection']), 100);

        $attributeRepository = $this->createMock(AttributeRepositoryInterface::class);
        $attributeRepository->method('getAttributeTypeByCodes')->willReturnCallback(
            fn(array $codes): array => \array_intersect_key(self::ATTRIBUTE_TYPES, \array_flip($codes))
        );

        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->productFieldFilter = $this->createMock(FilterInterface::class);
        $this->productFieldFilter->method('filter')->willReturn([]);
        $this->product = $this->createMock(ProductInterface::class);

        $this->sut = new EntityWithValuesFilter(
            $this->normalizer,
            $comparatorRegistry,
            $attributeRepository,
            $this->productFieldFilter,
            ['family', 'enabled', 'groups', 'categories', 'parent']
        );
    }

    public function test_zero_values_are_kept_when_the_product_has_none_of_these_values(): void
    {
        $this->givenTheStoredValuesAre([]);

        $this->assertSame(
            ['values' => $this->zeroValues()],
            $this->sut->filter($this->product, ['values' => $this->zeroValues()])
        );
    }

    public function test_zero_values_are_kept_when_the_stored_values_are_empty(): void
    {
        $this->givenTheStoredValuesAre([
            'a_number' => [['locale' => null, 'scope' => null, 'data' => null]],
            'a_metric' => [['locale' => null, 'scope' => null, 'data' => ['amount' => null, 'unit' => null]]],
            'a_price' => [['locale' => null, 'scope' => null, 'data' => []]],
        ]);

        $this->assertSame(
            ['values' => $this->zeroValues()],
            $this->sut->filter($this->product, ['values' => $this->zeroValues()])
        );
    }

    public function test_zero_values_equal_to_the_stored_zero_values_are_filtered_out(): void
    {
        $this->givenTheStoredValuesAre([
            'a_number' => [['locale' => null, 'scope' => null, 'data' => '0.0000']],
            'a_metric' => [['locale' => null, 'scope' => null, 'data' => ['amount' => '0.0000', 'unit' => 'KILOWATT']]],
            'a_price' => [['locale' => null, 'scope' => null, 'data' => [['amount' => '0.00', 'currency' => 'EUR']]]],
        ]);

        $this->assertSame([], $this->sut->filter($this->product, ['values' => $this->zeroValues()]));
    }

    public function test_only_the_zero_values_replacing_other_stored_values_are_kept(): void
    {
        $this->givenTheStoredValuesAre([
            'a_number' => [['locale' => null, 'scope' => null, 'data' => '5.0000']],
            'a_metric' => [['locale' => null, 'scope' => null, 'data' => ['amount' => '0.0000', 'unit' => 'KILOWATT']]],
            'a_price' => [['locale' => null, 'scope' => null, 'data' => [['amount' => '10.00', 'currency' => 'EUR']]]],
        ]);

        $zeroValues = $this->zeroValues();

        $this->assertSame(
            ['values' => ['a_number' => $zeroValues['a_number'], 'a_price' => $zeroValues['a_price']]],
            $this->sut->filter($this->product, ['values' => $zeroValues])
        );
    }

    public function test_the_entity_fields_are_delegated_to_the_field_filter(): void
    {
        $this->givenTheStoredValuesAre([]);
        $productFieldFilter = $this->createMock(FilterInterface::class);
        $productFieldFilter->expects($this->once())
            ->method('filter')
            ->with($this->product, ['family' => 'familyA'])
            ->willReturn(['family' => 'familyA']);
        $sut = new EntityWithValuesFilter(
            $this->normalizer,
            new ComparatorRegistry(),
            $this->createMock(AttributeRepositoryInterface::class),
            $productFieldFilter,
            ['family']
        );

        $this->assertSame(['family' => 'familyA'], $sut->filter($this->product, ['family' => 'familyA']));
    }

    public function test_it_throws_when_a_field_cannot_be_filtered(): void
    {
        $this->givenTheStoredValuesAre([]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot filter value of field "unknown_field"');

        $this->sut->filter($this->product, ['unknown_field' => 'foo']);
    }

    public function test_it_throws_when_an_attribute_is_unknown(): void
    {
        $this->givenTheStoredValuesAre([]);

        $this->expectException(UnknownAttributeException::class);

        $this->sut->filter(
            $this->product,
            ['values' => ['unknown_attribute' => [['locale' => null, 'scope' => null, 'data' => '0']]]]
        );
    }

    public function test_it_throws_when_a_value_is_not_an_array(): void
    {
        $this->givenTheStoredValuesAre([]);

        $this->expectException(InvalidPropertyTypeException::class);

        $this->sut->filter($this->product, ['values' => ['a_number' => ['0']]]);
    }

    private function givenTheStoredValuesAre(array $values): void
    {
        $this->normalizer->method('normalize')
            ->with($this->product, 'standard')
            ->willReturn(['identifier' => 'my_product', 'values' => $values]);
    }

    /**
     * The shapes the edit form posts for a 0 typed in a number, a metric and a price field.
     */
    private function zeroValues(): array
    {
        return [
            'a_number' => [['locale' => null, 'scope' => null, 'data' => '0']],
            'a_metric' => [['locale' => null, 'scope' => null, 'data' => ['amount' => '0', 'unit' => 'KILOWATT']]],
            'a_price' => [['locale' => null, 'scope' => null, 'data' => [['amount' => '0', 'currency' => 'EUR']]]],
        ];
    }
}
