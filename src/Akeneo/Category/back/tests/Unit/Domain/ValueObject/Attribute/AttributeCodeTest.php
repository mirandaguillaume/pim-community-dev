<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject\Attribute;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use PHPUnit\Framework\TestCase;

class AttributeCodeTest extends TestCase
{
    public function test_it_creates_a_valid_code(): void
    {
        $code = new AttributeCode('valid_code_123');
        $this->assertSame('valid_code_123', (string) $code);
    }

    public function test_it_rejects_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AttributeCode('');
    }

    public function test_it_rejects_uppercase_characters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('akeneo.category.validation.attribute.code.wrong_format');
        new AttributeCode('InvalidCode');
    }

    public function test_it_rejects_special_characters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('akeneo.category.validation.attribute.code.wrong_format');
        new AttributeCode('code-with-dashes');
    }

    public function test_it_rejects_code_longer_than_100_chars(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AttributeCode(str_repeat('a', 101));
    }

    public function test_it_accepts_code_of_exactly_100_chars(): void
    {
        $code = new AttributeCode(str_repeat('a', 100));
        $this->assertSame(str_repeat('a', 100), (string) $code);
    }

    public function test_it_accepts_underscores_and_digits(): void
    {
        $code = new AttributeCode('my_code_42');
        $this->assertSame('my_code_42', (string) $code);
    }
}
