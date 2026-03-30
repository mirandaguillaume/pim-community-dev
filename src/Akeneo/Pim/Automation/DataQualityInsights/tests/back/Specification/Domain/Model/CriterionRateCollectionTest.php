<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\CriterionRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PHPUnit\Framework\TestCase;

class CriterionRateCollectionTest extends TestCase
{
    private CriterionRateCollection $sut;

    protected function setUp(): void
    {
        $this->sut = new CriterionRateCollection();
    }

    public function test_it_is_a_rates_collection(): void
    {
        $this->assertInstanceOf(CriterionRateCollection::class, $this->sut);
    }

    public function test_it_adds_rates_per_channel_and_locale(): void
    {
        $this->sut->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(91))
                    ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(40))
                    ->addRate(new ChannelCode('print'), new LocaleCode('en_US'), new Rate(75))
                    ->addRate(new ChannelCode('print'), new LocaleCode('fr_FR'), new Rate(65));
        $this->assertSame([
                    'ecommerce' => [
                        'en_US' => 91,
                        'fr_FR' => 40,
                    ],
                    'print' => [
                        'en_US' => 75,
                        'fr_FR' => 65,
                    ],
                ], $this->sut->toArrayInt());
        $this->assertSame([
                    'ecommerce' => [
                        'en_US' => 'A',
                        'fr_FR' => 'E',
                    ],
                    'print' => [
                        'en_US' => 'C',
                        'fr_FR' => 'D',
                    ],
                ], $this->sut->toArrayString());
    }

    public function test_it_returns_the_rate_for_a_channel_and_a_locale(): void
    {
        $expectedRate = new Rate(75);
        $this->sut->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(91))
                    ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(40))
                    ->addRate(new ChannelCode('print'), new LocaleCode('en_US'), $expectedRate)
                    ->addRate(new ChannelCode('print'), new LocaleCode('fr_FR'), new Rate(65));
        $this->assertSame($expectedRate, $this->sut->getByChannelAndLocale(new ChannelCode('print'), new LocaleCode('en_US')));
    }

    public function test_it_returns_null_if_there_is_no_rate_for_a_channel_and_a_locale(): void
    {
        $this->sut->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(91))
                    ->addRate(new ChannelCode('print'), new LocaleCode('fr_FR'), new Rate(65));
        $this->assertNull($this->sut->getByChannelAndLocale(new ChannelCode('print'), new LocaleCode('en_US')));
    }
}
