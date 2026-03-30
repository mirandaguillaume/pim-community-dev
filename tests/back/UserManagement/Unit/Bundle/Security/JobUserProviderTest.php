<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Bundle\Security;

use Akeneo\UserManagement\Bundle\Security\JobUserProvider;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\InMemoryUser;

class JobUserProviderTest extends TestCase
{
    private UserRepositoryInterface|MockObject $userRepository;
    private JobUserProvider $sut;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->sut = new JobUserProvider($this->userRepository);
    }

    public function test_it_loads_a_user_by_its_username(): void
    {
        $julia = $this->createMock(UserInterface::class);

        $julia->method('isJobUser')->willReturn(true);
        $julia->method('isEnabled')->willReturn(true);
        $this->userRepository->method('findOneByIdentifier')->with('julia')->willReturn($julia);
        $this->assertSame($julia, $this->sut->loadUserByUsername('julia'));
    }

    public function test_it_throws_an_exception_if_username_does_not_exist(): void
    {
        $this->userRepository->method('findOneByIdentifier')->with('jean-pacôme')->willReturn(null);
        $this->expectException(UserNotFoundException::class);
        $this->sut->loadUserByIdentifier('jean-pacôme');
    }

    public function test_it_throws_an_exception_if_user_is_disabled(): void
    {
        $disabledGuy = $this->createMock(UserInterface::class);

        $disabledGuy->method('isJobUser')->willReturn(true);
        $disabledGuy->method('isEnabled')->willReturn(false);
        $this->userRepository->method('findOneByIdentifier')->with('disabled-guy')->willReturn($disabledGuy);
        $this->expectException(DisabledException::class);
        $this->sut->loadUserByIdentifier('disabled-guy');
    }

    public function test_it_throws_an_exception_if_user_is_not_job_user(): void
    {
        $apiUser = $this->createMock(UserInterface::class);

        $apiUser->method('isJobUser')->willReturn(false);
        $this->userRepository->method('findOneByIdentifier')->with('job-user')->willReturn($apiUser);
        $this->expectException(UserNotFoundException::class);
        $this->sut->loadUserByIdentifier('job-user');
    }

    public function test_it_refreshes_a_user(): void
    {
        $julia = $this->createMock(UserInterface::class);

        $this->userRepository->method('find')->with(42)->willReturn($julia);
        $julia->method('getId')->willReturn(42);
        $julia->method('isJobUser')->willReturn(true);
        $this->assertSame($julia, $this->sut->refreshUser($julia));
    }

    public function test_it_throw_an_exception_if_user_class_is_not_supported(): void
    {
        $julia = new InMemoryUser('julia', 'jambon');
        $this->expectException(UnsupportedUserException::class);
        $this->sut->refreshUser($julia);
    }

    public function test_it_throws_an_exception_if_user_cannot_be_refreshed(): void
    {
        $julia = $this->createMock(UserInterface::class);

        $julia->method('getId')->willReturn(42);
        $this->userRepository->method('find')->with(42)->willReturn(null);
        $this->expectException(UserNotFoundException::class);
        $this->sut->refreshUser($julia);
    }
}
