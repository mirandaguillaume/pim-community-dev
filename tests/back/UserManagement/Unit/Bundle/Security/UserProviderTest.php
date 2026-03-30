<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Bundle\Security;

use Akeneo\UserManagement\Bundle\Security\UserProvider;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\InMemoryUser;

class UserProviderTest extends TestCase
{
    private UserProvider $sut;

    protected function setUp(): void
    {
        $this->sut = new UserProvider();
    }

}
