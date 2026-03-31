<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Tool\Bundle\VersioningBundle\Event\BuildVersionEvent;
use Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber\AddUserSubscriber;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AddUserSubscriberTest extends TestCase
{
    private TokenStorageInterface|MockObject $tokenStorage;
    private TokenInterface|MockObject $token;
    private AuthorizationCheckerInterface|MockObject $authorizationChecker;
    private AddUserSubscriber $sut;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->token = $this->createMock(TokenInterface::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->sut = new AddUserSubscriber($this->authorizationChecker, $this->tokenStorage);
        $this->authorizationChecker->method('isGranted')->with($this->anything())->willReturn(true);
    }

    public function test_it_injects_current_username_into_the_version_manager(): void
    {
        $event = $this->createMock(BuildVersionEvent::class);
        $user = $this->createMock(User::class);

        $this->tokenStorage->method('getToken')->willReturn($this->token);
        $this->token->method('getUser')->willReturn($user);
        $user->method('getUserIdentifier')->willReturn('foo');
        $this->sut->preBuild($event);
    }

    public function test_it_does_nothing_if_a_token_is_not_present_in_the_security_context(): void
    {
        $event = $this->createMock(BuildVersionEvent::class);

        $this->tokenStorage->method('getToken')->willReturn(null);
        $this->sut->preBuild($event);
    }
}
