<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Domain\ValueObject;

use Akeneo\Category\Domain\ValueObject\Code;
use PHPUnit\Framework\TestCase;

class CodeTest extends TestCase
{
    public function test_it_creates_a_valid_code(): void
    {
        $code = new Code('my_code');
        $this->assertSame('my_code', (string) $code);
    }

    public function test_it_rejects_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Code('');
    }
}
