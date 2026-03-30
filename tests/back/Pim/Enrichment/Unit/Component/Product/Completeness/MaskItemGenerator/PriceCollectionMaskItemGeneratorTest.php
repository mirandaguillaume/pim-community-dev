<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Completeness\MaskItemGenerator;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\FindActivatedCurrenciesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\MaskItemGeneratorForAttributeType;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator\PriceCollectionMaskItemGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceCollectionMaskItemGeneratorTest extends TestCase
{
    private FindActivatedCurrenciesInterface|MockObject $findActivatedCurrencies;
    private PriceCollectionMaskItemGenerator $sut;

    protected function setUp(): void
    {
        $this->findActivatedCurrencies = $this->createMock(FindActivatedCurrenciesInterface::class);
        $this->sut = new PriceCollectionMaskItemGenerator($this->findActivatedCurrencies);
        $this->findActivatedCurrencies->method('forChannel')->with('channelCode')->willReturn(['USD', 'EUR', 'GPB']);
        $this->findActivatedCurrencies->method('forAllChannelsIndexedByChannelCode')->willReturn([
        'channelCode' => ['USD', 'EUR', 'GPB'],
        'otherChannelCode' => ['USD', 'EUR', 'GPB', 'AZN', 'AND', 'BRL', 'CAD', 'CNY', 'NZD', 'CZK', 'DOP', 'FJD', 'GEL', 'GTQ', 'HUF', 'INR', 'JMD', 'LAK', 'CHF', 'MRU', 'MAD'],
        ]);
    }

    public function test_it_is_a_mask_item_generator(): void
    {
        $this->assertInstanceOf(MaskItemGeneratorForAttributeType::class, $this->sut);
    }

    public function test_it_adds_ordered_currencies_to_mask_for_a_scopable_attribute(): void
    {
        $value = [
                    ['amount' => 200, 'currency' => 'USD'],
                    ['amount' => 100, 'currency' => 'EUR'],
                    ['amount' => 50, 'currency' => 'GPB'],
                ];
        $this->assertSame([
                        'attributeCode-EUR-GPB-USD-channelCode-localeCode',
                    ], $this->sut->forRawValue('attributeCode', 'channelCode', 'localeCode', $value));
    }

    public function test_it_adds_ordered_currencies_to_mask_for_a_non_scopable_attribute(): void
    {
        $value = [
                    ['amount' => 200, 'currency' => 'USD'],
                    ['amount' => 3000, 'currency' => 'DOP'],
                    ['amount' => 50, 'currency' => 'GPB'],
                ];
        $this->assertSame([
                         'attributeCode-GPB-USD-<all_channels>-localeCode',
                         'attributeCode-DOP-GPB-USD-<all_channels>-localeCode',
                     ], $this->sut->forRawValue('attributeCode', '<all_channels>', 'localeCode', $value));
    }

    public function test_it_filters_empty_prices(): void
    {
        $value = [
                    ['amount' => null, 'currency' => 'USD'],
                    ['amount' => 100, 'currency' => 'EUR']
                ];
        $this->assertSame([
                        'attributeCode-EUR-channelCode-localeCode',
                    ], $this->sut->forRawValue('attributeCode', 'channelCode', 'localeCode', $value));
    }

    public function test_it_filters_non_active_currencies_for_channel(): void
    {
        $value = [
                    ['amount' => 200, 'currency' => 'USD'],
                    ['amount' => 100, 'currency' => 'CNY'],
                    ['amount' => 50, 'currency' => 'GPB'],
                ];
        $this->assertSame([
                        'attributeCode-GPB-USD-channelCode-localeCode',
                    ], $this->sut->forRawValue('attributeCode', 'channelCode', 'localeCode', $value));
    }

    public function test_it_filters_non_existing_channel(): void
    {
        $value = [
                    ['amount' => 200, 'currency' => 'USD'],
                    ['amount' => 100, 'currency' => 'EUR'],
                    ['amount' => 50, 'currency' => 'GPB'],
                ];
        $this->assertSame([], $this->sut->forRawValue('attributeCode', 'nonExistingChannel', 'localeCode', $value));
    }

    public function test_it_adds_ordered_currencies_to_mask_with_a_lot_of_currencies(): void
    {
        $value = [
                    ['amount' => 200, 'currency' => 'USD'],
                    ['amount' => 100, 'currency' => 'EUR'],
                    ['amount' => 50, 'currency' => 'GPB'],
                    ['amount' => 50, 'currency' => 'AZN'],
                    ['amount' => 50, 'currency' => 'AND'],
                    ['amount' => 50, 'currency' => 'CAD'],
                    ['amount' => 50, 'currency' => 'CNY'],
                    ['amount' => 50, 'currency' => 'NZD'],
                    ['amount' => 50, 'currency' => 'DOP'],
                    ['amount' => 50, 'currency' => 'FJD'],
                    ['amount' => 50, 'currency' => 'GEL'],
                    ['amount' => 50, 'currency' => 'GTQ'],
                    ['amount' => 50, 'currency' => 'HUF'],
                    ['amount' => 50, 'currency' => 'INR'],
                    ['amount' => 50, 'currency' => 'JMD'],
                    ['amount' => 50, 'currency' => 'LAK'],
                    ['amount' => 50, 'currency' => 'CHF'],
                    ['amount' => 50, 'currency' => 'MRU'],
                    ['amount' => 50, 'currency' => 'MAD'],
                    ['amount' => 60, 'currency' => 'UNKNOWN'],
                ];
        $this->assertSame([
                         'attributeCode-EUR-GPB-USD-<all_channels>-localeCode',
                         'attributeCode-AND-AZN-CAD-CHF-CNY-DOP-EUR-FJD-GEL-GPB-GTQ-HUF-INR-JMD-LAK-MAD-MRU-NZD-USD-<all_channels>-localeCode',
                     ], $this->sut->forRawValue('attributeCode', '<all_channels>', 'localeCode', $value));
    }
}
