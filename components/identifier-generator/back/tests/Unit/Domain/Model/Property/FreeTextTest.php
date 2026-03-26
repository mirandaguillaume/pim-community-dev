<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FreeTextTest extends TestCase
{
    private FreeText $sut;

    protected function setUp(): void
    {
        $this->sut = FreeText::fromString('ABC');
    }

    public function test_it_is_a_free_text(): void
    {
        $this->assertInstanceOf(FreeText::class, $this->sut);
    }

    public function test_it_cannot_be_instantiated_with_an_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        FreeText::fromString('');
    }

    public function test_it_returns_a_free_text(): void
    {
        $this->assertSame('ABC', $this->sut->asString());
    }

    public function test_it_normalize_a_free_text(): void
    {
        $this->assertSame([
                    'type' => 'free_text',
                    'string' => 'ABC',
                ], $this->sut->normalize());
    }

    public function test_it_creates_from_normalized(): void
    {
        $this->assertEquals(FreeText::fromString('ABC'), $this->sut->fromNormalized([
                    'type' => 'free_text',
                    'string' => 'ABC',
                ]));
    }

    public function test_it_throws_an_exception_when_type_is_bad(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        FreeText::fromNormalized([
                    'type' => 'bad',
                    'string' => 'ABC',
                ]);
    }

    public function test_it_throws_an_exception_when_type_key_is_missing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        FreeText::fromNormalized([
                    'string' => 'ABC',
                ]);
    }

    public function test_it_throws_an_exception_from_normalized_with_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        FreeText::fromNormalized([
                    'type' => 'free_text',
                    'string' => '',
                ]);
    }
}
