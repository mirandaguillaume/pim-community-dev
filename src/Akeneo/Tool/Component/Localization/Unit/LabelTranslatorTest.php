<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Localization;

use Akeneo\Tool\Component\Localization\LabelTranslator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\Translator;

class LabelTranslatorTest extends TestCase
{
    private Translator|MockObject $translator;
    private LabelTranslator $sut;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(Translator::class);
        $this->sut = new LabelTranslator($this->translator);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(LabelTranslator::class, $this->sut);
    }

    public function test_it_translates_labels_and_returns_fallback_if_not_found(): void
    {
        $catalogue = $this->createMock(MessageCatalogueInterface::class);

        $this->translator->method('getCatalogue')->with('fr_FR')->willReturn($catalogue);
        $catalogue->method('defines')->with('some.key')->willReturn(true);
        $catalogue->method('defines')->with('not.found')->willReturn(false);
        $this->translator->method('trans')->with('some.key', [], null, 'fr_FR')->willReturn('une traduction');
        $this->assertSame('une traduction', $this->sut->translate('some.key', 'fr_FR', '[fallback]'));
        $this->assertSame('[fallback]', $this->sut->translate('not.found', 'fr_FR', '[fallback]'));
    }
}
