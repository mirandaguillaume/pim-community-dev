<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Acceptance\InMemory;

use Akeneo\Channel\API\Query\IsLocaleEditable;
use Akeneo\Test\Acceptance\User\InMemoryUserRepository;
use Akeneo\Test\Channel\Acceptance\InMemory\InMemoryIsLocaleEditable;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\TestCase;

class InMemoryIsLocaleEditableTest extends TestCase
{
    private InMemoryIsLocaleEditable $sut;

    protected function setUp(): void
    {
        $userRepository = new InMemoryUserRepository();
        $adminGroup = new Group('admin');
        $allGroup = new Group('all');
        $noGroupUser = new User();
        $noGroupUser->setId(1);
        $noGroupUser->setUsername('no_group_user');
        $userRepository->save($noGroupUser);
        $adminUser = new User();
        $adminUser->setId(2);
        $adminUser->setUsername('admin_user');
        $adminUser->addGroup($adminGroup);
        $adminUser->addGroup($allGroup);
        $userRepository->save($adminUser);
        $basicUser = new User();
        $basicUser->setId(3);
        $basicUser->setUsername('basic_user');
        $basicUser->addGroup($allGroup);
        $userRepository->save($basicUser);

        $this->sut = new InMemoryIsLocaleEditable($userRepository);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(InMemoryIsLocaleEditable::class, $this->sut);
        $this->assertInstanceOf(IsLocaleEditable::class, $this->sut);
    }

    public function test_it_returns_all_activated_locale_codes(): void
    {
        $this->sut->addEditableLocaleCodeForGroup('admin', 'en_US');
        $this->assertSame(false, $this->sut->forUserId('en_US', 1));
        $this->assertSame(false, $this->sut->forUserId('fr_FR', 1));
        $this->assertSame(true, $this->sut->forUserId('en_US', 2));
        $this->assertSame(false, $this->sut->forUserId('fr_FR', 2));
        $this->assertSame(false, $this->sut->forUserId('en_US', 3));
        $this->assertSame(false, $this->sut->forUserId('fr_FR', 3));
        $this->assertSame(false, $this->sut->forUserId('en_US', 99));
    }
}
