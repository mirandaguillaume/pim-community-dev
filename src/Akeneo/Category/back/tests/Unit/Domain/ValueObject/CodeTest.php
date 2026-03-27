<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject;

use Akeneo\Category\Domain\ValueObject\Code;
use PHPUnit\Framework\TestCase;

class CodeTest extends TestCase
{
    public function testItCreatesAValidCode(): void
    {
        $code = new Code('my_code');
        $this->assertSame('my_code', (string) $code);
    }

    public function testItRejectsEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Code('');
    }
}
