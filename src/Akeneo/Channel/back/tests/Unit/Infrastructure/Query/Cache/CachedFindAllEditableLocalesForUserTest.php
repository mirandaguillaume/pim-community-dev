<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\Infrastructure\Query\Cache;

use Akeneo\Channel\API\Query\FindAllEditableLocalesForUser;
use Akeneo\Channel\API\Query\Locale;
use Akeneo\Channel\Infrastructure\Query\Cache\CachedFindAllEditableLocalesForUser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CachedFindAllEditableLocalesForUserTest extends TestCase
{
    private FindAllEditableLocalesForUser|MockObject $findAllEditableLocalesForUser;
    private CachedFindAllEditableLocalesForUser $sut;

    protected function setUp(): void
    {
        $this->findAllEditableLocalesForUser = $this->createMock(FindAllEditableLocalesForUser::class);
        $this->sut = new CachedFindAllEditableLocalesForUser($this->findAllEditableLocalesForUser);
    }

    public function test_it_finds_all_editable_locale_for_user_and_caches_it(): void
    {
        $this->findAllEditableLocalesForUser
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
