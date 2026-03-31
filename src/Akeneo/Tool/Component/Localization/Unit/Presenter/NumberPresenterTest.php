<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Localization\Presenter;

use Akeneo\Tool\Component\Localization\Factory\NumberFactory;
use Akeneo\Tool\Component\Localization\Presenter\NumberPresenter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NumberPresenterTest extends TestCase
{
    private NumberFactory|MockObject $numberFactory;
    private NumberPresenter $sut;

    protected function setUp(): void
    {
        $this->numberFactory = $this->createMock(NumberFactory::class);
        $this->sut = new NumberPresenter($this->numberFactory, ['pim_catalog_number']);
    }

    public function test_it_supports_numbers(): void
    {
        $this->assertSame(true, $this->sut->supports('pim_catalog_number'));
        $this->assertSame(false, $this->sut->supports('foobar'));
    }

    public function test_it_presents_english_number(): void
    {
        $numberFormatter = $this->createMock(NumberFormatter::class);

        $this->numberFactory->method('create')->with([])->willReturn($numberFormatter);
        $numberFormatter->method('format')->with(12000.34)->willReturn('12,000.34');
        $numberFormatter->method('setAttribute')->with($this->anything(), $this->anything())->willReturn(true);
        $this->assertSame('12,000.34', $this->sut->present(12000.34));
    }

    public function test_it_presents_french_number(): void
    {
        $numberFormatter = $this->createMock(NumberFormatter::class);

        $this->numberFactory->method('create')->with(['locale' => 'fr_FR'])->willReturn($numberFormatter);
        $numberFormatter->method('format')->with(12000.34)->willReturn('12 000,34');
        $numberFormatter->method('setAttribute')->with($this->anything(), $this->anything())->willReturn(true);
        $this->assertSame('12 000,34', $this->sut->present(12000.34, ['locale' => 'fr_FR']));
    }

    public function test_it_disables_grouping_separator(): void
    {
        $numberFormatter = $this->createMock(NumberFormatter::class);

        $this->numberFactory->method('create')->with(['disable_grouping_separator' => true])->willReturn($numberFormatter);
        $numberFormatter->method('setSymbol')->with(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '')->willReturn(null);
        $numberFormatter->method('format')->with(12000.34)->willReturn('12000.34');
        $numberFormatter->method('setAttribute')->with($this->anything(), $this->anything())->willReturn(true);
        $this->assertSame('12000.34', $this->sut->present(12000.34, ['disable_grouping_separator' => true]));
    }

    public function test_it_presents_a_number_with_very_long_decimal(): void
    {
        $numberFormatter = $this->createMock(NumberFormatter::class);

        $this->numberFactory->method('create')->with(['locale' => 'fr_FR'])->willReturn($numberFormatter);
        $numberFormatter->method('format')->with(12000.3400887897676)->willReturn('12 000,3400887897676');
        $numberFormatter->method('setAttribute')->with($this->anything(), $this->anything())->willReturn(true);
        $this->assertSame('12 000,3400887897676', $this->sut->present(12000.3400887897676, ['locale' => 'fr_FR']));
    }
}
