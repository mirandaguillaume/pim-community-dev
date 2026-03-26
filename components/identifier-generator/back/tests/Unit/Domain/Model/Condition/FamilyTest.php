<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Family;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyTest extends TestCase
{
    private Family $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_a_family(): void
    {
        $this->assertTrue(is_a(Family::class, ConditionInterface::class, true));
        $this->assertTrue(is_a(Family::class, Family::class, true));
    }

    public function test_it_should_throw_exception_if_type_is_not_family(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Family::fromNormalized([
                    'type' => 'bad',
                    'operator' => 'EMPTY',
                ]);
    }

    public function test_it_should_throw_exception_if_no_operator_is_defined(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Family::fromNormalized([
                    'type' => 'family',
                    'value' => ['shirts'],
                ]);
    }

    public function test_it_should_throw_exception_if_operator_is_not_a_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Family::fromNormalized([
                    'type' => 'family',
                    'operator' => true,
                ]);
    }

    public function test_it_should_throw_exception_if_value_is_not_defined(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Family::fromNormalized([
                    'type' => 'family',
                    'operator' => 'IN',
                ]);
    }

    public function test_it_should_throw_exception_if_value_is_not_an_array_of_strings(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Family::fromNormalized([
                    'type' => 'family',
                    'operator' => 'IN',
                    'value' => [true],
                ]);
    }

    public function test_it_should_throw_exception_if_value_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Family::fromNormalized([
                    'type' => 'family',
                    'operator' => 'IN',
                    'value' => [],
                ]);
    }

    public function test_it_should_throw_exception_if_value_defined(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Family::fromNormalized([
                    'type' => 'family',
                    'operator' => 'EMPTY',
                    'value' => ['shirts'],
                ]);
    }

    public function test_it_should_normalize_without_value(): void
    {
        $this->sut = Family::fromNormalized([
                    'type' => 'family',
                    'operator' => 'EMPTY'
                ]);
        $this->assertSame([
                    'type' => 'family',
                    'operator' => 'EMPTY',
                ], $this->sut->normalize());
    }

    public function test_it_should_normalize_with_value(): void
    {
        $this->sut = Family::fromNormalized([
                    'type' => 'family',
                    'operator' => 'IN',
                    'value' => ['shirts']
                ]);
        $this->assertSame([
                    'type' => 'family',
                    'operator' => 'IN',
                    'value' => ['shirts'],
                ], $this->sut->normalize());
    }
}
