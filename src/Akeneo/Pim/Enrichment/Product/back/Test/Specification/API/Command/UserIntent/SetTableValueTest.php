<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTableValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetTableValueTest extends TestCase
{
    private SetTableValue $sut;

    protected function setUp(): void
    {
        $this->sut = new SetTableValue(
            'nutrition',
            'ecommerce',
            'en_US',
            [
                ['ingredient' => 'salt'],
                ['ingredient' => 'egg', 'quantity' => 2],
            ]
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetTableValue::class, $this->sut);
        $this->assertInstanceOf(ValueUserIntent::class, $this->sut);
    }

    public function test_it_returns_the_attribute_code(): void
    {
        $this->assertSame('nutrition', $this->sut->attributeCode());
    }

    public function test_it_returns_the_locale_code(): void
    {
        $this->assertSame('en_US', $this->sut->localeCode());
    }

    public function test_it_returns_the_channel_code(): void
    {
        $this->assertSame('ecommerce', $this->sut->channelCode());
    }

    public function test_it_returns_the_table_value(): void
    {
        $this->assertEquals([
                        ['ingredient' => 'salt'],
                        ['ingredient' => 'egg', 'quantity' => 2],
                    ], $this->sut->tableValue());
    }

    public function test_it_must_be_instantiated_with_valid_data_structure(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SetTableValue(
            'nutrition',
            'ecommerce',
            'en_US',
            ['ingredient' => 'salt']
        );
        $this->expectException(\InvalidArgumentException::class);
        new SetTableValue(
            'nutrition',
            'ecommerce',
            'en_US',
            [
                        'wrong_index_1' => ['ingredient' => 'salt'],
                        'wrong_index_2' => ['ingredient' => 'egg', 'quantity' => 2],
                    ]
        );
    }

    public function test_it_can_be_instantiated_with_integer_column_codes(): void
    {
        $this->sut = new SetTableValue(
            'nutrition',
            'ecommerce',
            'en_US',
            [
                        ['ingredient' => 'egg', 42 => 'michel', 420 => 12],
                    ]
        );
        $this->assertEquals([
                        ['ingredient' => 'egg', 42 => 'michel', 420 => 12],
                    ], $this->sut->tableValue());
    }
}
