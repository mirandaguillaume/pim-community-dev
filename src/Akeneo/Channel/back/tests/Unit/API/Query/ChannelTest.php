<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\API\Query;

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\ConversionUnitCollection;
use Akeneo\Channel\API\Query\LabelCollection;
use PHPUnit\Framework\TestCase;

class ChannelTest extends TestCase
{
    private Channel $sut;

    protected function setUp(): void
    {
        $this->sut = new Channel('mobile',
            ['fr_FR', 'uk_UA'],
            LabelCollection::fromArray(['fr_FR' => 'Mobile', 'uk_UA' => 'смартфон']),
            ['EUR', 'USD'],
            ConversionUnitCollection::fromArray(['a_measurement_attribute' => 'GRAM', 'another_measurement_attribute' => 'POUND']));
    }

    public function test_it_has_getters(): void
    {
        $this->assertSame('mobile', $this->sut->getCode());
        $this->assertSame(['fr_FR', 'uk_UA'], $this->sut->getLocaleCodes());
        $this->assertEquals(LabelCollection::fromArray(['fr_FR' => 'Mobile', 'uk_UA' => 'смартфон']), $this->sut->getLabels());
        $this->assertSame(['EUR', 'USD'], $this->sut->getActiveCurrencies());
        $this->assertEquals(ConversionUnitCollection::fromArray(['a_measurement_attribute' => 'GRAM', 'another_measurement_attribute' => 'POUND']), $this->sut->getConversionUnits());
    }

    public function test_it_tells_if_a_given_locale_is_active(): void
    {
        $this->assertSame(true, $this->sut->isLocaleActive('fr_FR'));
        $this->assertSame(true, $this->sut->isLocaleActive('uk_UA'));
        $this->assertSame(false, $this->sut->isLocaleActive('en_US'));
    }

    public function test_it_tells_if_a_given_currency_is_active(): void
    {
        $this->assertSame(true, $this->sut->isCurrencyActive('EUR'));
        $this->assertSame(true, $this->sut->isCurrencyActive('USD'));
        $this->assertSame(false, $this->sut->isCurrencyActive('GBP'));
    }
}
