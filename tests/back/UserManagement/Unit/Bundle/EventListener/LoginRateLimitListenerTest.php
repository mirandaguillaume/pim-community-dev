<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Bundle\EventListener;

use Akeneo\UserManagement\Bundle\EventListener\LoginRateLimitListener;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Bundle\Model\LockedAccountException;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginRateLimitListenerTest extends TestCase
{
    private const ACCOUNT_LOCK_DURATION = 2;
    private const ALLOWED_FAILED_ATTEMPTS = 10;

    private UserManager|MockObject $userManager;
    private LoginRateLimitListener $sut;

    protected function setUp(): void
    {
        $this->userManager = $this->createMock(UserManager::class);
        $this->sut = new LoginRateLimitListener($this->userManager, self::ACCOUNT_LOCK_DURATION, self::ALLOWED_FAILED_ATTEMPTS, false);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(LoginRateLimitListener::class, $this->sut);
    }

    public function test_it_can_authenticate_when_counter_is_reset(): void
    {
        $passport = $this->createMock(Passport::class);
        $badge = $this->createMock(UserBadge::class);
        $user = $this->createMock(UserInterface::class);
        $event = $this->createMock(CheckPassportEvent::class);

        $this->configureUser($user, 0, null);
        $this->configurePassport($passport, $badge, $user);
        $event->method('getPassport')->willReturn($passport);
        $this->sut->checkPassport($event);
        $this->addToAssertionCount(1);
    }

    public function test_it_can_authenticate_under_max_limit_counter(): void
    {
        $passport = $this->createMock(Passport::class);
        $badge = $this->createMock(UserBadge::class);
        $user = $this->createMock(UserInterface::class);
        $event = $this->createMock(CheckPassportEvent::class);

        $this->configureUser($user, self::ALLOWED_FAILED_ATTEMPTS - 1, $this->getAuthenticationFailureResetDateFromNow(1));
        $this->configurePassport($passport, $badge, $user);
        $event->method('getPassport')->willReturn($passport);
        $this->sut->checkPassport($event);
        $this->addToAssertionCount(1);
    }

    public function test_it_rejects_authentication_when_limit_is_reached(): void
    {
        $passport = $this->createMock(Passport::class);
        $badge = $this->createMock(UserBadge::class);
        $user = $this->createMock(UserInterface::class);
        $event = $this->createMock(CheckPassportEvent::class);

        $this->configureUser($user, self::ALLOWED_FAILED_ATTEMPTS, $this->getAuthenticationFailureResetDateFromNow(self::ACCOUNT_LOCK_DURATION - 1));
        $this->configurePassport($passport, $badge, $user);
        $event->method('getPassport')->willReturn($passport);
        $this->expectException(LockedAccountException::class);
        $this->sut->checkPassport($event);
    }

    public function test_it_rejects_authentication_when_user_just_reach_max_attempt(): void
    {
        $passport = $this->createMock(Passport::class);
        $badge = $this->createMock(UserBadge::class);
        $user = $this->createMock(UserInterface::class);
        $event = $this->createMock(CheckPassportEvent::class);

        $this->configureUser($user, self::ALLOWED_FAILED_ATTEMPTS, $this->getAuthenticationFailureResetDateFromNow(self::ACCOUNT_LOCK_DURATION - 1));
        $this->configurePassport($passport, $badge, $user);
        $event->method('getPassport')->willReturn($passport);
        $this->expectException(LockedAccountException::class);
        $this->sut->checkPassport($event);
    }

    public function test_it_increase_failed_attempts_counter_on_login_failure(): void
    {
        $passport = $this->createMock(Passport::class);
        $badge = $this->createMock(UserBadge::class);
        $user = $this->createMock(UserInterface::class);
        $event = $this->createMock(LoginFailureEvent::class);

        $this->configureUser($user, self::ALLOWED_FAILED_ATTEMPTS - 1, $this->getAuthenticationFailureResetDateFromNow(self::ACCOUNT_LOCK_DURATION - 1));
        $this->configurePassport($passport, $badge, $user);
        $event->method('getPassport')->willReturn($passport);
        $user->expects($this->once())->method('setConsecutiveAuthenticationFailureCounter')->with(self::ALLOWED_FAILED_ATTEMPTS);
        $this->userManager->expects($this->once())->method('updateUser')->with($user);
        $this->sut->onFailureLogin($event);
    }

    public function test_it_consider_login_has_failed_when_passport_is_empty(): void
    {
        $event = $this->createMock(LoginFailureEvent::class);

        $event->method('getPassport')->willReturn(null);
        $this->assertNull($this->sut->onFailureLogin($event));
    }

    public function test_it_consider_login_has_failed_when_passport_is_not_a_symfony_passport_instance(): void
    {
        $passport = $this->createMock(Passport::class);
        $event = $this->createMock(LoginFailureEvent::class);

        $event->method('getPassport')->willReturn($passport);
        $passport->method('hasBadge')->with(UserBadge::class)->willReturn(false);
        $this->assertNull($this->sut->onFailureLogin($event));
    }

    public function test_it_reset_failed_attempts_on_login_success(): void
    {
        $user = $this->createMock(UserInterface::class);
        $event = $this->createMock(LoginSuccessEvent::class);

        $this->configureUser($user, 0, $this->getAuthenticationFailureResetDateFromNow(self::ACCOUNT_LOCK_DURATION + 1));
        $event->method('getUser')->willReturn($user);
        $user->expects($this->once())->method('setConsecutiveAuthenticationFailureCounter')->with(0);
        $user->expects($this->once())->method('setAuthenticationFailureResetDate')->with(null);
        $this->userManager->expects($this->once())->method('updateUser')->with($user);
        $this->sut->onSuccessfulLogin($event);
    }

    private function configureUser(UserInterface|MockObject $user, int $consecutiveAuthenticationFailureCounter, ?\DateTime $authenticationFailureResetDate): void
    {
        $user->method('getConsecutiveAuthenticationFailureCounter')->willReturn($consecutiveAuthenticationFailureCounter);
        $user->method('getAuthenticationFailureResetDate')->willReturn($authenticationFailureResetDate);
        $user->method('getRoles')->willReturn([]);
        $user->method('getPassword')->willReturn('');
        $user->method('getSalt')->willReturn('');
    }

    private function configurePassport(Passport|MockObject $passport, UserBadge|MockObject $badge, UserInterface|MockObject $user): void
    {
        $passport->method('hasBadge')->with(UserBadge::class)->willReturn(true);
        $passport->method('getBadge')->with(UserBadge::class)->willReturn($badge);
        $badge->method('getUser')->willReturn($user);
    }

    private function getAuthenticationFailureResetDateFromNow(int $minutesBehind): \DateTime
    {
        return (new \DateTime())->modify("-{$minutesBehind} minute");
    }
}
