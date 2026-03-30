<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Localization;

use Akeneo\Tool\Component\Localization\LanguageTranslator;
use PHPUnit\Framework\TestCase;

class LanguageTranslatorTest extends TestCase
{
    private LanguageTranslator $sut;

    protected function setUp(): void
    {
        $this->sut = new LanguageTranslator();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(LanguageTranslator::class, $this->sut);
    }

    public function test_it_translates_languages(): void
    {
        $this->assertSame('français France', $this->sut->translate('fr_FR', 'fr', '[français]'));
        $this->assertSame('English United States', $this->sut->translate('en_US', 'en', '[english]'));
        $this->assertSame('English United Kingdom', $this->sut->translate('en_GB', 'en', '[english]'));
        $this->assertSame('Englisch Vereinigtes Königreich', $this->sut->translate('en_GB', 'de', '[english]'));
    }

    public function test_it_returns_fallback_when_not_found(): void
    {
        $this->assertSame('[this is unknown]', $this->sut->translate('en_GB', 'unknown', '[this is unknown]'));
        $this->assertSame('[unknown language]', $this->sut->translate('UNKNOWN_FR', 'fr', '[unknown language]'));
    }

    public function test_it_returns_fallback_when_intl_can_not_translate_the_country_name_into_the_given_locale(): void
    {
        $this->assertSame('[bs_Cyrl_BA]', $this->sut->translate('bs_Cyrl_BA', 'en_US', '[bs_Cyrl_BA]'));
    }
}
