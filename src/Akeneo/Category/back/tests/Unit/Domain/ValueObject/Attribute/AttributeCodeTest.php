<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject\Attribute;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use PHPUnit\Framework\TestCase;

class AttributeCodeTest extends TestCase
{
    public function testItCreatesAValidCode(): void
    {
        $code = new AttributeCode('valid_code_123');
        $this->assertSame('valid_code_123', (string) $code);
    }

    public function testItRejectsEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AttributeCode('');
    }

    public function testItRejectsUppercaseCharacters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('akeneo.category.validation.attribute.code.wrong_format');
        new AttributeCode('InvalidCode');
    }

    public function testItRejectsSpecialCharacters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('akeneo.category.validation.attribute.code.wrong_format');
        new AttributeCode('code-with-dashes');
    }

    public function testItRejectsCodeLongerThan100Chars(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AttributeCode(str_repeat('a', 101));
    }

    public function testItAcceptsCodeOfExactly100Chars(): void
    {
        $code = new AttributeCode(str_repeat('a', 100));
        $this->assertSame(str_repeat('a', 100), (string) $code);
    }

    public function testItAcceptsUnderscoresAndDigits(): void
    {
        $code = new AttributeCode('my_code_42');
        $this->assertSame('my_code_42', (string) $code);
    }
}
