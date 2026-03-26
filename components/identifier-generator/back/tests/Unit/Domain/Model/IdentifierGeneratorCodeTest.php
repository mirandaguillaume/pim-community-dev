<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierGeneratorCodeTest extends TestCase
{
    private IdentifierGeneratorCode $sut;

    protected function setUp(): void
    {
        $this->sut = IdentifierGeneratorCode::fromString('abcdef');
    }

    public function test_it_is_a_identifier_generator(): void
    {
        $this->assertInstanceOf(IdentifierGeneratorCode::class, $this->sut);
    }

    public function test_it_cannot_be_instantiated_with_an_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        IdentifierGeneratorCode::fromString('');
    }

    public function test_it_returns_a_code(): void
    {
        $this->assertSame('abcdef', $this->sut->asString());
    }
}
