<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Query\Cache;

use Akeneo\Channel\API\Query\FindAllViewableLocalesForUser;
use Akeneo\Channel\API\Query\Locale;
use Akeneo\Channel\Infrastructure\Query\Cache\CachedFindAllViewableLocalesForUser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CachedFindAllViewableLocalesForUserTest extends TestCase
{
    private FindAllViewableLocalesForUser|MockObject $findAllViewableLocalesForUser;
    private CachedFindAllViewableLocalesForUser $sut;

    protected function setUp(): void
    {
        $this->findAllViewableLocalesForUser = $this->createMock(FindAllViewableLocalesForUser::class);
        $this->sut = new CachedFindAllViewableLocalesForUser($this->findAllViewableLocalesForUser);
    }

    public function test_it_finds_all_viewable_locale_for_user_and_caches_it(): void
    {
        $this->findAllViewableLocalesForUser
            ->expects($this->exactly(2))
            ->method('findAll')
            ->willReturnMap([
                [1, [new Locale('en_US', true)]],
                [2, [new Locale('en_US', true)]],
            ]);

        $this->sut->findAll(1);
        $this->sut->findAll(1);
        $this->sut->findAll(1);
        $this->sut->findAll(2);
        $this->sut->findAll(2);
    }
}
