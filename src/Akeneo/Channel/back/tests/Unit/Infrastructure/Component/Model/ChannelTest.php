<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Component\Model;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelTranslation;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelTest extends TestCase
{
    private Channel $sut;

    protected function setUp(): void
    {
        $this->sut = new Channel();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(Channel::class, $this->sut);
    }

    public function test_it_gets_a_translation_even_if_the_locale_case_is_wrong(): void
    {
        $translationEn = $this->createMock(ChannelTranslation::class);

        $translationEn->method('getLocale')->willReturn('EN_US');
        $this->sut->addTranslation($translationEn);
        $this->assertSame($translationEn, $this->sut->getTranslation('en_US'));
    }

    public function test_it_returns_itself_when_setting_the_code(): void
    {
        $this->assertSame($this->sut, $this->sut->setCode('ecommerce'));
        $this->assertSame('ecommerce', $this->sut->getCode());
    }

    public function test_it_returns_itself_when_adding_a_translation(): void
    {
        $this->assertSame($this->sut, $this->sut->addTranslation($this->createMock(ChannelTranslation::class)));
    }

    public function test_it_has_no_locale_until_one_is_added(): void
    {
        $locale = $this->createMock(LocaleInterface::class);

        $this->assertFalse($this->sut->hasLocale($locale));
    }

    public function test_it_adds_a_locale_and_registers_the_channel_on_it(): void
    {
        $locale = $this->createMock(LocaleInterface::class);
        $locale->expects($this->once())->method('addChannel')->with($this->sut);

        $result = $this->sut->addLocale($locale);

        $this->assertSame($this->sut, $result);
        $this->assertTrue($this->sut->hasLocale($locale));
    }

    public function test_it_does_not_add_the_same_locale_twice(): void
    {
        $locale = $this->createMock(LocaleInterface::class);
        $locale->expects($this->once())->method('addChannel')->with($this->sut);

        $this->sut->addLocale($locale);
        $this->sut->addLocale($locale);

        $this->assertCount(1, $this->sut->getLocales());
    }

    public function test_it_removes_a_locale_and_unregisters_the_channel(): void
    {
        $locale = $this->createMock(LocaleInterface::class);
        $locale->method('addChannel');
        $this->sut->addLocale($locale);
        $locale->expects($this->once())->method('removeChannel')->with($this->sut);

        $result = $this->sut->removeLocale($locale);

        $this->assertSame($this->sut, $result);
        $this->assertFalse($this->sut->hasLocale($locale));
    }

    public function test_it_does_nothing_when_removing_a_locale_that_was_never_added(): void
    {
        $locale = $this->createMock(LocaleInterface::class);
        $locale->expects($this->never())->method('removeChannel');

        $this->sut->removeLocale($locale);
    }
}
