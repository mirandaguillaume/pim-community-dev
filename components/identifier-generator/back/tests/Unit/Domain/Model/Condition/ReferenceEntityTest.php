<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ReferenceEntity;
use PHPUnit\Framework\TestCase;

class ReferenceEntityTest extends TestCase
{
    private ReferenceEntity $sut;

    protected function setUp(): void {}

    public function test_it_should_throw_exception_if_type_is_not_ref_entity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ReferenceEntity::fromNormalized([
            'type' => 'simple_select',
            'operator' => 'NOT EMPTY',
        ]);
    }

    public function test_it_should_throw_exception_if_no_attribute_code_is_defined(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ReferenceEntity::fromNormalized([
            'type' => 'reference_entity',
            'operator' => 'EMPTY',
        ]);
    }

    public function test_it_should_throw_exception_if_attribute_code_is_not_a_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ReferenceEntity::fromNormalized([
            'type' => 'reference_entity',
            'operator' => 'EMPTY',
            'attributeCode' => true,
        ]);
    }

    public function test_it_should_throw_exception_if_scope_is_not_a_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ReferenceEntity::fromNormalized([
            'type' => 'reference_entity',
            'operator' => 'EMPTY',
            'attributeCode' => 'brand',
            'scope' => true,
        ]);
    }

    public function test_it_should_throw_exception_if_locale_is_not_a_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ReferenceEntity::fromNormalized([
            'type' => 'reference_entity',
            'operator' => 'EMPTY',
            'attributeCode' => 'brand',
            'locale' => true,
        ]);
    }

    public function test_it_should_throw_exception_if_no_operator_is_defined(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ReferenceEntity::fromNormalized([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
        ]);
    }

    public function test_it_should_throw_exception_if_operator_is_not_a_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ReferenceEntity::fromNormalized([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'operator' => true,
        ]);
    }

    public function test_it_should_throw_exception_if_operator_is_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ReferenceEntity::fromNormalized([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'operator' => 'UNKNOWN',
        ]);
    }

    public function test_it_should_normalize(): void
    {
        $this->sut = ReferenceEntity::fromNormalized([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'operator' => 'NOT EMPTY',
        ]);
        $this->assertSame([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'operator' => 'NOT EMPTY',
        ], $this->sut->normalize());
    }

    public function test_it_should_normalize_with_scope_and_locale(): void
    {
        $this->sut = ReferenceEntity::fromNormalized([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'operator' => 'NOT EMPTY',
            'scope' => 'ecommerce',
            'locale' => 'en_US',
        ]);
        $this->assertSame([
            'type' => 'reference_entity',
            'attributeCode' => 'brand',
            'operator' => 'NOT EMPTY',
            'scope' => 'ecommerce',
            'locale' => 'en_US',
        ], $this->sut->normalize());
    }
}
