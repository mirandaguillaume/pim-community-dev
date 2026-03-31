<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Localization\Presenter;

use Akeneo\Tool\Component\Localization\Factory\DateFactory;
use Akeneo\Tool\Component\Localization\Presenter\DatePresenter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DatePresenterTest extends TestCase
{
    private DateFactory|MockObject $dateFactory;
    private DatePresenter $sut;

    protected function setUp(): void
    {
        $this->dateFactory = $this->createMock(DateFactory::class);
        $this->sut = new DatePresenter($this->dateFactory, ['pim_catalog_date']);
    }

    public function test_it_supports_metric(): void
    {
        $this->assertSame(true, $this->sut->supports('pim_catalog_date'));
        $this->assertSame(false, $this->sut->supports('foobar'));
    }

    public function test_it_presents_an_english_date(): void
    {
        $dateFormatter = $this->createMock(IntlDateFormatter::class);

        $date = '2015-01-31';
        $datetime = new \DateTime('2015-01-31');
        $options = ['locale' => 'en_US'];
        $this->dateFactory->method('create')->with($options)->willReturn($dateFormatter);
        $dateFormatter->method('format')->with($datetime)->willReturn('01/31/2015');
        $this->assertSame('01/31/2015', $this->sut->present($date, $options));
    }

    public function test_it_presents_a_french_date(): void
    {
        $dateFormatter = $this->createMock(IntlDateFormatter::class);

        $date = '2015-01-31';
        $datetime = new \DateTime('2015-01-31');
        $options = ['locale' => 'fr_FR'];
        $this->dateFactory->method('create')->with($options)->willReturn($dateFormatter);
        $dateFormatter->method('format')->with($datetime)->willReturn('31/01/2015');
        $this->assertSame('31/01/2015', $this->sut->present($date, $options));
    }

    public function test_it_does_not_present_a_date_if_the_date_can_not_be_formatted(): void
    {
        $date = '-001-11-30T00:00:00+00:00';
        $options = [
                    'locale' => 'fr_FR',
                    'date_format' => 'dd/MM/yyyy',
                    'datetype'    => \IntlDateFormatter::SHORT,
                    'timetype'    => \IntlDateFormatter::NONE,
                    'timezone'    => null,
                    'calendar'    => null,
                ];
        $this->assertNull($this->sut->present($date, $options));
    }
}
