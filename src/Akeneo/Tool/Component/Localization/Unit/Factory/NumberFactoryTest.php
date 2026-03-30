<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Localization\Factory;

use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Localization\Factory\NumberFactory;

class NumberFactoryTest extends TestCase
{
    private NumberFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new NumberFactory([
            'zz_ZZ' => '#,##0.00-test-¤;(#,##0.00¤)',
        ]);
    }

    public function test_it_creates_a_default_currency_formatter(): void
    {
        $this->assertSame('12,34 €', $this->sut->create(['locale' => 'fr_FR', 'type' => \NumberFormatter::CURRENCY])->formatCurrency(12.34, 'EUR'));
    }

    public function test_it_creates_a_defined_currency_formatter(): void
    {
        $this->assertSame('12.34-test-€', $this->sut->create(['locale' => 'zz_ZZ', 'type' => \NumberFormatter::CURRENCY])->formatCurrency(12.34, 'EUR'));
        $this->assertSame('(12.34€)', $this->sut->create(['locale' => 'zz_ZZ', 'type' => \NumberFormatter::CURRENCY])->formatCurrency(-12.34, 'EUR'));
    }

    public function test_it_creates_without_locale(): void
    {
        $this->assertSame('€12.34', $this->sut->create(['type' => \NumberFormatter::CURRENCY])->formatCurrency(12.34, 'EUR'));
    }
}
