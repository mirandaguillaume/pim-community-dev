<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ReferenceEntity;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\SimpleSelect;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\ReferenceEntityProperty;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceEntityPropertyTest extends TestCase
{
    private ReferenceEntityProperty $sut;

    protected function setUp(): void
    {
        $this->sut = ReferenceEntityProperty::fromNormalized([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'process' => ['type' => 'truncate', 'operator' => '=', 'value' => 3],
            'scope' => 'ecommerce',
            'locale' => 'en_US',
        ]);
    }

    public function test_it_is_a_reference_entity_property(): void
    {
        $this->assertInstanceOf(ReferenceEntityProperty::class, $this->sut);
    }

    public function test_it_returns_a_type(): void
    {
        $this->assertSame('reference_entity', $this->sut->type());
    }

    public function test_it_returns_a_process(): void
    {
        $process = Process::fromNormalized(['type' => 'truncate', 'operator' => '=', 'value' => 3]);
        $this->assertInstanceOf(Process::class, $this->sut->process());
        $this->assertEquals($process, $this->sut->process());
    }

    public function test_it_normalizes_itself(): void
    {
        $this->assertSame([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'process' => [
                'type' => 'truncate',
                'operator' => '=',
                'value' => 3,
            ],
            'scope' => 'ecommerce',
            'locale' => 'en_US',
        ], $this->sut->normalize());
    }

    public function test_it_normalizes_itself_with_scope_and_locale(): void
    {
        $this->sut = ReferenceEntityProperty::fromNormalized([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'process' => ['type' => 'truncate', 'operator' => '=', 'value' => 3],
            'scope' => 'ecommerce',
            'locale' => 'en_US',
        ]);
        $this->assertSame([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'process' => [
                'type' => 'truncate',
                'operator' => '=',
                'value' => 3,
            ],
            'scope' => 'ecommerce',
            'locale' => 'en_US',
        ], $this->sut->normalize());
    }

    public function test_it_should_return_an_implicit_condition(): void
    {
        $this->assertEquals(ReferenceEntity::fromNormalized([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'operator' => 'NOT EMPTY',
            'scope' => 'ecommerce',
            'locale' => 'en_US',
        ]), $this->sut->getImplicitCondition());
    }

    public function test_it_normalizes_without_scope_and_locale(): void
    {
        $property = ReferenceEntityProperty::fromNormalized([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'process' => ['type' => 'truncate', 'operator' => '=', 'value' => 3],
        ]);
        $normalized = $property->normalize();
        $this->assertSame([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'process' => [
                'type' => 'truncate',
                'operator' => '=',
                'value' => 3,
            ],
        ], $normalized);
        $this->assertArrayNotHasKey('scope', $normalized);
        $this->assertArrayNotHasKey('locale', $normalized);
    }

    public function test_it_normalizes_with_scope_only(): void
    {
        $property = ReferenceEntityProperty::fromNormalized([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'process' => ['type' => 'truncate', 'operator' => '=', 'value' => 3],
            'scope' => 'ecommerce',
        ]);
        $normalized = $property->normalize();
        $this->assertSame('ecommerce', $normalized['scope']);
        $this->assertArrayNotHasKey('locale', $normalized);
    }

    public function test_it_normalizes_with_locale_only(): void
    {
        $property = ReferenceEntityProperty::fromNormalized([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'process' => ['type' => 'truncate', 'operator' => '=', 'value' => 3],
            'locale' => 'en_US',
        ]);
        $normalized = $property->normalize();
        $this->assertArrayNotHasKey('scope', $normalized);
        $this->assertSame('en_US', $normalized['locale']);
    }

    public function test_it_returns_the_attribute_code(): void
    {
        $this->assertSame('brand', $this->sut->attributeCode());
    }

    public function test_it_returns_implicit_condition_without_scope_and_locale(): void
    {
        $property = ReferenceEntityProperty::fromNormalized([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'process' => ['type' => 'truncate', 'operator' => '=', 'value' => 3],
        ]);
        $this->assertEquals(ReferenceEntity::fromNormalized([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'operator' => 'NOT EMPTY',
            'scope' => null,
            'locale' => null,
        ]), $property->getImplicitCondition());
    }
}
