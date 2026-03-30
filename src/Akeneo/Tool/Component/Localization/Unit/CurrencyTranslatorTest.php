<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Localization;

use Akeneo\Tool\Component\Localization\CurrencyTranslator;
use PHPUnit\Framework\TestCase;

class CurrencyTranslatorTest extends TestCase
{
    private CurrencyTranslator $sut;

    protected function setUp(): void
    {
        $this->sut = new CurrencyTranslator();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CurrencyTranslator::class, $this->sut);
    }

    public function test_it_translates_currencies(): void
    {
        $this->assertSame('euro', $this->sut->translate('EUR', 'fr_FR', 'euros'));
        $this->assertSame('Euro', $this->sut->translate('EUR', 'en_US', 'euros'));
        $this->assertSame('couronne danoise', $this->sut->translate('DKK', 'fr_FR', 'danish crown'));
        $this->assertSame('Danish Krone', $this->sut->translate('DKK', 'en_US', 'DKK'));
    }

    public function test_it_returns_fallback_when_not_found(): void
    {
        $this->assertSame('devise inconnue', $this->sut->translate('UNKNOWN', 'fr_FR', 'devise inconnue'));
        $this->assertSame('pays inconnu', $this->sut->translate('EUR', 'some_LOCALE', 'pays inconnu'));
    }
}
