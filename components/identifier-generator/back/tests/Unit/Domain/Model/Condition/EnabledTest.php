<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnabledTest extends TestCase
{
    private Enabled $sut;

    protected function setUp(): void
    {
        $this->sut = Enabled::fromBoolean(true);
    }

    public function test_it_is_a_enabled(): void
    {
        $this->assertInstanceOf(Enabled::class, $this->sut);
    }

    public function test_it_normalize_an_enabled(): void
    {
        $this->assertSame([
                    'type' => 'enabled',
                    'value' => true,
                ], $this->sut->normalize());
    }

    public function test_it_creates_from_normalized(): void
    {
        $this->assertEquals(Enabled::fromBoolean(false), $this->sut->fromNormalized([
                    'type' => 'enabled',
                    'value' => false,
                ]));
    }

    public function test_it_throws_an_exception_when_type_is_bad(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Enabled::fromNormalized([
                    'type' => 'bad',
                    'value' => true,
                ]);
    }

    public function test_it_throws_an_exception_when_type_key_is_missing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Enabled::fromNormalized([
                    'value' => 'ABC',
                ]);
    }

    public function test_it_throws_an_exception_from_normalized_with_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Enabled::fromNormalized([
                    'type' => 'enabled',
                    'value' => 'abc',
                ]);
    }
}
