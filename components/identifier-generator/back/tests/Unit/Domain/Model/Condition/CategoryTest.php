<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Category;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\CategoryOperator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTest extends TestCase
{
    private Category $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_a_category(): void
    {
        $this->assertTrue(is_a(Category::class, ConditionInterface::class, true));
        $this->assertTrue(is_a(Category::class, Category::class, true));
    }

    public function test_it_cant_be_instanciated_if_type_is_not_category(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Category::fromNormalized([
                    'type' => 'bad',
                    'operator' => 'IN',
                    'value' => ['tshirts'],
                ]);
    }

    public function test_it_cant_be_instanciated_if_no_operator_is_defined(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Category::fromNormalized([
                    'type' => 'category',
                    'value' => ['tshirts'],
                ]);
    }

    public function test_it_cant_be_instanciated_if_operator_is_not_a_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Category::fromNormalized([
                    'type' => 'category',
                    'operator' => true,
                    'value' => ['tshirts'],
                ]);
    }

    public function test_it_cant_be_instanciated_if_operator_is_unknown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Category::fromNormalized([
                    'type' => 'category',
                    'operator' => 'EMPTY',
                    'value' => ['tshirts'],
                ]);
    }

    public function test_it_cant_be_instanciated_if_value_is_not_defined(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Category::fromNormalized([
                    'type' => 'category',
                    'operator' => 'IN',
                ]);
    }

    public function test_it_cant_be_instanciated_if_value_is_not_an_array_of_strings(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Category::fromNormalized([
                    'type' => 'category',
                    'operator' => 'IN',
                    'value' => [true],
                ]);
    }

    public function test_it_cant_be_instanciated_if_value_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Category::fromNormalized([
                    'type' => 'category',
                    'operator' => 'IN',
                    'value' => [],
                ]);
    }

    public function test_it_can_be_normalized_with_value_and_in_operator(): void
    {
        $this->sut = Category::fromNormalized([
                    'type' => 'category',
                    'operator' => 'IN',
                    'value' => ['pants', 'shoes'],
                ]);
        $this->assertSame([
                    'type' => 'category',
                    'operator' => 'IN',
                    'value' => ['pants', 'shoes'],
                ], $this->sut->normalize());
    }

    public function test_it_can_be_normalized_without_value_and_classified_operator(): void
    {
        $this->sut = Category::fromNormalized([
                    'type' => 'category',
                    'operator' => 'CLASSIFIED',
                ]);
        $this->assertSame([
                    'type' => 'category',
                    'operator' => 'CLASSIFIED',
                ], $this->sut->normalize());
    }
}
