<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class ChannelLocaleRateCollectionTest extends TestCase
{
    private ChannelLocaleRateCollection $sut;

    protected function setUp(): void
    {
        $this->sut = new ChannelLocaleRateCollection();
    }

    public function test_it_can_be_constructed_from_an_array_of_rates_as_integer(): void
    {
        $this->sut = ChannelLocaleRateCollection::fromArrayInt([
                    'mobile' => [
                        'en_US' => 87,
                        'fr_FR' => 34,
                    ],
                    'print' => [
                        'en_US' => 42,
                    ],
                ]);
        $expectedRates = [
                    'mobile' => [
                        'en_US' => new Rate(87),
                        'fr_FR' => new Rate(34),
                    ],
                    'print' => [
                        'en_US' => new Rate(42),
                    ],
                ];
        $rates = \iterator_to_array($this->sut);
        $this->assertEquals($expectedRates, $rates);
    }

    public function test_it_can_be_constructed_from_an_array_of_normalized_rates(): void
    {
        $this->sut = ChannelLocaleRateCollection::fromNormalizedRates([
                    'mobile' => [
                        'en_US' => [
                            'rank' => 5,
                            'value' => 42,
                        ],
                        'fr_FR' => [
                            'rank' => 2,
                            'value' => 86,
                        ],
                    ],
                    'print' => [
                        'en_US' => [
                            'rank' => 3,
                            'value' => 73,
                        ],
                    ],
                ]);
        $expectedRates = [
                    'mobile' => [
                        'en_US' => new Rate(42),
                        'fr_FR' => new Rate(86),
                    ],
                    'print' => [
                        'en_US' => new Rate(73),
                    ],
                ];
        $rates = \iterator_to_array($this->sut);
        $this->assertEquals($expectedRates, $rates);
    }

    public function test_it_throws_an_exception_if_it_is_constructed_from_malformed_normalized_rates(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ChannelLocaleRateCollection::fromNormalizedRates([
                    'mobile' => [
                        'en_US' => [
                            'rank' => 5,
                            'value' => 42,
                        ],
                        'fr_FR' => [
                            'rank' => 2,
                        ],
                    ],
                ]);
    }

    public function test_it_returns_the_rate_for_a_channel_and_locale(): void
    {
        $rateMobileEn = new Rate(42);
        $rateMobileFr = new Rate(56);
        $ratePrintEn = new Rate(73);
        $this->sut->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), $rateMobileEn);
        $this->sut->addRate(new ChannelCode('mobile'), new LocaleCode('fr_FR'), $rateMobileFr);
        $this->sut->addRate(new ChannelCode('print'), new LocaleCode('en_US'), $ratePrintEn);
        $this->assertSame($rateMobileFr, $this->sut->getByChannelAndLocale(new ChannelCode('mobile'), new LocaleCode('fr_FR')));
    }

    public function test_it_returns_the_rates_in_a_normalized_format(): void
    {
        $rateMobileEn = new Rate(42);
        $rateMobileFr = new Rate(86);
        $ratePrintEn = new Rate(73);
        $this->sut->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), $rateMobileEn);
        $this->sut->addRate(new ChannelCode('mobile'), new LocaleCode('fr_FR'), $rateMobileFr);
        $this->sut->addRate(new ChannelCode('print'), new LocaleCode('en_US'), $ratePrintEn);
        $this->assertSame([
                    'mobile' => [
                        'en_US' => [
                            'rank' => 5,
                            'value' => 42,
                        ],
                        'fr_FR' => [
                            'rank' => 2,
                            'value' => 86,
                        ],
                    ],
                    'print' => [
                        'en_US' => [
                            'rank' => 3,
                            'value' => 73,
                        ],
                    ],
                ], $this->sut->toNormalizedRates());
    }
}
