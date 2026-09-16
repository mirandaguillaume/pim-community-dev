<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Bundle\EventListener;

use Akeneo\UserManagement\Bundle\EventListener\LoginSubscriber;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoginSubscriberTest extends TestCase
{
    private UserManager|MockObject $userManager;
    private LoginSubscriber $sut;

    protected function setUp(): void
    {
        $this->userManager = $this->createMock(UserManager::class);
        $this->sut = new LoginSubscriber($this->userManager);
    }

    public function test_it_records_the_login_of_a_pim_user(): void
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getLoginCount')->willReturn(4);
        $user->expects($this->once())->method('setLastLogin')
            ->with($this->callback(fn(\DateTime $date): bool => \abs(\time() - $date->getTimestamp()) < 5 && 'UTC' === $date->getTimezone()->getName()))
            ->willReturnSelf();
        $user->expects($this->once())->method('setLoginCount')->with(5)->willReturnSelf();
        $this->userManager->expects($this->once())->method('updateUser')->with($user);

        $this->sut->onLogin($this->loginEventFor($user));
    }

    public function test_it_does_nothing_for_a_user_that_is_not_a_pim_user(): void
    {
        $user = $this->createMock(SecurityUserInterface::class);
        $this->userManager->expects($this->never())->method('updateUser');

        $this->sut->onLogin($this->loginEventFor($user));
    }

    private function loginEventFor(SecurityUserInterface $user): InteractiveLoginEvent
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        return new InteractiveLoginEvent(Request::create('/'), $token);
    }
}
