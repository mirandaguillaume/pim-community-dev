<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Security;

use Akeneo\Connectivity\Connection\Application\Apps\Security\FindCurrentAppIdInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\FindCurrentAppId;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class FindCurrentAppIdTest extends TestCase
{
    private TokenStorageInterface|MockObject $tokenStorage;
    private FindCurrentAppId $sut;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->sut = new FindCurrentAppId($this->tokenStorage);
    }

    public function test_it_is_a_find_user_app_id(): void
    {
        $this->assertInstanceOf(FindCurrentAppId::class, $this->sut);
        $this->assertInstanceOf(FindCurrentAppIdInterface::class, $this->sut);
    }

    public function test_it_finds_user_app_id(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $user->method('getProperty')->with('app_id')->willReturn('an_app_id');
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $this->assertSame('an_app_id', $this->sut->execute());
    }

    public function test_it_returns_null_if_app_id_is_not_set(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $user->method('getProperty')->with('app_id')->willReturn(null);
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $this->assertNull($this->sut->execute());
    }
}
