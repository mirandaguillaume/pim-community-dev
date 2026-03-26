<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DelimiterTest extends TestCase
{
    private Delimiter $sut;

    protected function setUp(): void
    {
        $this->sut = Delimiter::fromString('-');
    }

    public function test_it_is_a_delimiter(): void
    {
        $this->assertInstanceOf(Delimiter::class, $this->sut);
    }

    public function test_it_returns_a_delimiter(): void
    {
        $this->assertSame('-', $this->sut->asString());
    }

    public function test_it_cannot_be_instantiated_with_an_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Delimiter::fromString('');
    }

    public function test_it_returns_a_delimiter_with_null_value(): void
    {
        $this->sut = Delimiter::fromString(null);
        $this->assertNull($this->sut->asString());
    }

    public function test_it_cannot_be_instantiated_with_value_too_long(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Delimiter::fromString('abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz');
    }
}
