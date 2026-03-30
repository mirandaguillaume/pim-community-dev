<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumeric;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;

class IsNumericTest extends TestCase
{
    private IsNumeric $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_initializable(): void
    {
        $this->assertTrue(is_a(IsNumeric::class, IsNumeric::class, true));
    }

    public function test_it_is_a_validator_constraint(): void
    {
        $this->assertTrue(is_a(IsNumeric::class, Constraint::class, true));
    }

    public function test_it_provides_attribute_code(): void
    {
        $this->sut = new IsNumeric(['attributeCode' => 'weight']);
        $this->assertSame('weight', $this->sut->attributeCode);
    }

    public function test_it_provides_empty_string_if_no_attribute_code_has_been_provided(): void
    {
        $this->assertSame('', $this->sut->attributeCode);
    }
}
