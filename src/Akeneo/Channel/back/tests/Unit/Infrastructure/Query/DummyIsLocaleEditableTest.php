<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Query;

use Akeneo\Channel\API\Query\IsLocaleEditable;
use Akeneo\Channel\Infrastructure\Query\DummyIsLocaleEditable;
use PHPUnit\Framework\TestCase;

class DummyIsLocaleEditableTest extends TestCase
{
    private DummyIsLocaleEditable $sut;

    protected function setUp(): void
    {
        $this->sut = new DummyIsLocaleEditable();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(DummyIsLocaleEditable::class, $this->sut);
        $this->assertInstanceOf(IsLocaleEditable::class, $this->sut);
    }

    public function test_it_returns_always_true(): void
    {
        foreach (['en_US', 'fr_FR', 'de_DE'] as $localeCode) {
            $this->assertSame(true, $this->sut->forUserId($localeCode, 1));
            $this->assertSame(true, $this->sut->forUserId($localeCode, 4_638_765_483));
            $this->assertSame(true, $this->sut->forUserId($localeCode, 0));
        }
    }
}
