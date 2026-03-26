<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Query\Cache;

use Akeneo\Channel\API\Query\FindLocales;
use Akeneo\Channel\API\Query\Locale;
use Akeneo\Channel\Infrastructure\Query\Cache\CachedFindLocales;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CachedFindLocalesTest extends TestCase
{
    private FindLocales|MockObject $findLocales;
    private CachedFindLocales $sut;

    protected function setUp(): void
    {
        $this->findLocales = $this->createMock(FindLocales::class);
        $this->sut = new CachedFindLocales($this->findLocales);
    }

    public function test_it_finds_a_locale_by_its_code_and_caches_it(): void
    {
        $this->findLocales->expects($this->once())->method('find')->with('en_US')->willReturn(new Locale('en_US', true));
        $this->sut->find('en_US');
        $this->sut->find('en_US');
        $this->sut->find('en_US');
    }

    public function test_it_finds_all_activated_locales_and_caches_them(): void
    {
        $this->findLocales->expects($this->once())->method('findAllActivated')->willReturn([
            new Locale('en_US', true),
            new Locale('fr_FR', true),
        ]);
        $this->sut->findAllActivated();
        $this->sut->findAllActivated();
        $this->sut->findAllActivated();
    }
}
