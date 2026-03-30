<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveAssetValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveAssetValueTest extends TestCase
{
    private RemoveAssetValue $sut;

    protected function setUp(): void
    {
        $this->sut = new RemoveAssetValue('code', 'ecommerce', 'en_US', ['packshot1', 'packshot2']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(RemoveAssetValue::class, $this->sut);
        $this->assertInstanceOf(ValueUserIntent::class, $this->sut);
    }

    public function test_it_returns_the_attribute_code(): void
    {
        $this->assertSame('code', $this->sut->attributeCode());
    }

    public function test_it_returns_the_locale_code(): void
    {
        $this->assertSame('en_US', $this->sut->localeCode());
    }

    public function test_it_returns_the_channel_code(): void
    {
        $this->assertSame('ecommerce', $this->sut->channelCode());
    }

    public function test_it_returns_the_asset_codes(): void
    {
        $this->assertSame(['packshot1', 'packshot2'], $this->sut->assetCodes());
    }

    public function test_it_can_only_be_instantiated_with_string_asset_codes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RemoveAssetValue('name', 'ecommerce', 'en_US', ['test', 12, false]);
    }

    public function test_it_cannot_be_instantiated_with_empty_asset_codes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RemoveAssetValue('name', 'ecommerce', 'en_US', []);
    }

    public function test_it_cannot_be_instantiated_if_one_of_the_asset_codes_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RemoveAssetValue('name', 'ecommerce', 'en_US', ['a', '', 'b']);
    }
}
