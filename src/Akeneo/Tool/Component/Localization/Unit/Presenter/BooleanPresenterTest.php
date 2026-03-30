<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Localization\Presenter;

use Akeneo\Tool\Component\Localization\Presenter\BooleanPresenter;
use PHPUnit\Framework\TestCase;

class BooleanPresenterTest extends TestCase
{
    private BooleanPresenter $sut;

    protected function setUp(): void
    {
        $this->sut = new BooleanPresenter(['enabled']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(BooleanPresenter::class, $this->sut);
    }

    public function test_it_supports_enabled(): void
    {
        $this->assertSame(true, $this->sut->supports('enabled'));
        $this->assertSame(false, $this->sut->supports('yolo'));
    }

    public function test_it_presents_values(): void
    {
        $this->assertSame('true', $this->sut->present(true));
        $this->assertSame('true', $this->sut->present('true'));
        $this->assertSame('true', $this->sut->present('1'));
        $this->assertSame('true', $this->sut->present(1));
        $this->assertSame('false', $this->sut->present(false));
        $this->assertSame('false', $this->sut->present('false'));
        $this->assertSame('false', $this->sut->present('0'));
        $this->assertSame('false', $this->sut->present(0));
        $this->assertSame('', $this->sut->present('yolo'));
    }
}
