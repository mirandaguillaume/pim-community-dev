<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Security;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ConnectedPimUserProvider;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectedPimUserProviderTest extends TestCase
{
    private TokenStorageInterface|MockObject $tokenStorage;
    private ConnectedPimUserProvider $sut;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->sut = new ConnectedPimUserProvider($this->tokenStorage);
    }

    public function test_it_is_a_connected_pim_user_provider(): void
    {
        $this->sut->beAnInstanceOf(ConnectedPimUserProvider::class);
    }

    public function test_it_gets_current_user_id(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $userId = 1;
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $user->method('getId')->willReturn($userId);
        $this->assertSame($userId, $this->sut->getCurrentUserId());
    }

    public function test_it_throws_an_exception_if_user_is_not_connected(): void
    {
        $this->tokenStorage->method('getToken')->willReturn(null);
        $this->sut->shouldThrow(\LogicException::class)->during('getCurrentUserId');
    }
}
