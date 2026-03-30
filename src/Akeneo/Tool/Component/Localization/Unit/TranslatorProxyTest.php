<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Localization;

use Akeneo\Tool\Component\Localization\TranslatorProxy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatorProxyTest extends TestCase
{
    private TranslatorInterface|MockObject $translator;
    private TranslatorProxy $sut;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->sut = new TranslatorProxy($this->translator);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(TranslatorProxy::class, $this->sut);
    }

    public function test_it_presents_translated_metric_unit(): void
    {
        $this->translator->method('trans')->with('INCH', [], 'measures')->willReturn('Inch');
        $this->assertSame('Inch', $this->sut->trans('INCH', ['domain' => 'measures']));
    }
}
