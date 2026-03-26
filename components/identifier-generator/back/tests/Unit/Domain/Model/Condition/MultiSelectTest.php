<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\MultiSelect;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MultiSelectTest extends TestCase
{
    private MultiSelect $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_a_multi_select(): void
    {
        $this->assertTrue(is_a(MultiSelect::class, ConditionInterface::class, true));
        $this->assertTrue(is_a(MultiSelect::class, MultiSelect::class, true));
    }

    public function test_it_cant_be_instanciated_with_invalid_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MultiSelect::fromNormalized([
                    'type' => 'bad',
                    'operator' => 'EMPTY',
                ]);
    }

    public function test_it_cant_be_instanciated_if_no_attribute_code_is_defined(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'operator' => 'EMPTY',
                ]);
    }

    public function test_it_cant_be_instanciated_if_attribute_code_is_not_a_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'operator' => 'EMPTY',
                    'attributeCode' => true,
                ]);
    }

    public function test_it_cant_be_instanciated_if_scope_is_not_a_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'operator' => 'EMPTY',
                    'attributeCode' => 'color',
                    'scope' => true,
                ]);
    }

    public function test_it_cant_be_instanciated_if_locale_is_not_a_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'operator' => 'EMPTY',
                    'attributeCode' => 'color',
                    'locale' => true,
                ]);
    }

    public function test_it_cant_be_instanciated_if_no_operator_is_defined(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'attributeCode' => 'color',
                ]);
    }

    public function test_it_cant_be_instanciated_if_operator_is_not_a_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'attributeCode' => 'color',
                    'operator' => true,
                ]);
    }

    public function test_it_cant_be_instanciated_if_operator_is_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'attributeCode' => 'color',
                    'operator' => 'UNKNOWN',
                ]);
    }

    public function test_it_cant_be_instanciated_if_value_is_not_defined(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'attributeCode' => 'color',
                    'operator' => 'IN',
                ]);
    }

    public function test_it_cant_be_instanciated_if_value_is_not_an_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'attributeCode' => 'color',
                    'operator' => 'IN',
                    'value' => 'red',
                ]);
    }

    public function test_it_cant_be_instanciated_if_value_is_not_an_array_of_strings(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'attributeCode' => 'color',
                    'operator' => 'IN',
                    'value' => ['red', true],
                ]);
    }

    public function test_it_cant_be_instanciated_if_value_is_empty_for_in_operator(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'attributeCode' => 'color',
                    'operator' => 'IN',
                    'value' => [],
                ]);
    }

    public function test_it_cant_be_instanciated_if_value_is_defined_and_operator_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'attributeCode' => 'color',
                    'operator' => 'EMPTY',
                    'value' => ['red', 'blue'],
                ]);
    }

    public function test_it_can_be_normalized_with_value_and_in_operator(): void
    {
        $this->sut = MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'attributeCode' => 'color',
                    'operator' => 'IN',
                    'value' => ['red', 'blue'],
                ]);
        $this->assertSame([
                    'type' => 'multi_select',
                    'attributeCode' => 'color',
                    'operator' => 'IN',
                    'value' => ['red', 'blue']
                ], $this->sut->normalize());
    }

    public function test_it_can_be_normalized_without_value_and_empty_operator(): void
    {
        $this->sut = MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'attributeCode' => 'color',
                    'operator' => 'EMPTY',
                ]);
        $this->assertSame([
                    'type' => 'multi_select',
                    'attributeCode' => 'color',
                    'operator' => 'EMPTY',
                ], $this->sut->normalize());
    }

    public function test_it_can_be_normalized_with_scope_and_locale(): void
    {
        $this->sut = MultiSelect::fromNormalized([
                    'type' => 'multi_select',
                    'attributeCode' => 'color',
                    'operator' => 'EMPTY',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ]);
        $this->assertSame([
                    'type' => 'multi_select',
                    'attributeCode' => 'color',
                    'operator' => 'EMPTY',
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ], $this->sut->normalize());
    }
}
