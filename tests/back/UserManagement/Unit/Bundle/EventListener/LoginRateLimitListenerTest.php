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

        $this->sut->initUser($user, 0, null);
        $this->sut->initPassport($passport, $badge, $user);
        $event->method('getPassport')->willReturn($passport);
        $this->sut->checkPassport($event);
        $this->userManager->method('updateUser');
    }

    public function test_it_can_authenticate_under_max_limit_counter(): void
    {
        $passport = $this->createMock(Passport::class);
        $badge = $this->createMock(UserBadge::class);
        $user = $this->createMock(UserInterface::class);
        $event = $this->createMock(CheckPassportEvent::class);

        $this->sut->initUser($user, self::ALLOWED_FAILED_ATTEMPTS - 1, $this->getAuthenticationFailureResetDateFromNow(1));
        $this->sut->initPassport($passport, $badge, $user);
        $event->method('getPassport')->willReturn($passport);
        $this->sut->checkPassport($event);
    }

    public function test_it_rejects_authentication_when_limit_is_reached(): void
    {
        $passport = $this->createMock(Passport::class);
        $badge = $this->createMock(UserBadge::class);
        $user = $this->createMock(UserInterface::class);
        $event = $this->createMock(CheckPassportEvent::class);

        $this->sut->initUser($user, self::ALLOWED_FAILED_ATTEMPTS, $this->getAuthenticationFailureResetDateFromNow(self::ACCOUNT_LOCK_DURATION - 1));
        $this->sut->initPassport($passport, $badge, $user);
        $event->method('getPassport')->willReturn($passport);
        $this->sut->shouldThrow(new LockedAccountException(self::ACCOUNT_LOCK_DURATION))
                    ->duringCheckPassport($event);
    }

    public function test_it_rejects_authentication_when_user_just_reach_max_attempt(): void
    {
        $passport = $this->createMock(Passport::class);
        $badge = $this->createMock(UserBadge::class);
        $user = $this->createMock(UserInterface::class);
        $event = $this->createMock(CheckPassportEvent::class);

        $this->sut->initUser($user, self::ALLOWED_FAILED_ATTEMPTS, $this->getAuthenticationFailureResetDateFromNow(self::ACCOUNT_LOCK_DURATION - 1));
        $this->sut->initPassport($passport, $badge, $user);
        $event->method('getPassport')->willReturn($passport);
        $this->sut->shouldThrow(LockedAccountException::class)
                    ->duringCheckPassport($event);
    }

    public function test_it_increase_failed_attempts_counter_on_login_failure(): void
    {
        $passport = $this->createMock(Passport::class);
        $badge = $this->createMock(UserBadge::class);
        $user = $this->createMock(UserInterface::class);
        $event = $this->createMock(LoginFailureEvent::class);

        $this->sut->initUser($user,self::ALLOWED_FAILED_ATTEMPTS - 1, $this->getAuthenticationFailureResetDateFromNow(self::ACCOUNT_LOCK_DURATION - 1));
        $this->sut->initPassport($passport, $badge, $user);
        $event->method('getPassport')->willReturn($passport);
        $this->sut->onFailureLogin($event);
        $this->sut->lockStateShouldBeUpdated($this->userManager, $user, self::ALLOWED_FAILED_ATTEMPTS);
    }

    public function test_it_consider_login_has_failed_when_passport_is_empty(): void
    {
        $user = $this->createMock(UserInterface::class);
        $event = $this->createMock(LoginFailureEvent::class);

        $this->sut->initUser($user,self::ALLOWED_FAILED_ATTEMPTS - 1, $this->getAuthenticationFailureResetDateFromNow(self::ACCOUNT_LOCK_DURATION - 1));
        $event->method('getPassport')->willReturn(null);
        $this->assertNull($this->sut->onFailureLogin($event));
    }

    public function test_it_consider_login_has_failed_when_passport_is_not_a_symfony_passport_instance(): void
    {
        $passport = $this->createMock(Passport::class);
        $badge = $this->createMock(UserBadge::class);
        $user = $this->createMock(UserInterface::class);
        $event = $this->createMock(LoginFailureEvent::class);

        $this->sut->initUser($user,self::ALLOWED_FAILED_ATTEMPTS - 1, $this->getAuthenticationFailureResetDateFromNow(self::ACCOUNT_LOCK_DURATION - 1));
        $event->method('getPassport')->willReturn($passport);
        $passport->method('hasBadge')->with(UserBadge::class)->willReturn(false);
        $this->assertNull($this->sut->onFailureLogin($event));
    }

    public function test_it_reset_failed_attempts_on_login_success(): void
    {
        $user = $this->createMock(UserInterface::class);
        $event = $this->createMock(LoginSuccessEvent::class);

        $this->sut->initUser($user, 0, $this->getAuthenticationFailureResetDateFromNow(self::ACCOUNT_LOCK_DURATION + 1));
        $event->method('getUser')->willReturn($user);
        $this->sut->onSuccessfulLogin($event);
        $this->sut->lockStateShouldBeReset($user, $this->userManager);
    }

    private function initUser(UserInterface $user, int $consecutiveAuthenticationFailureCounter, \DateTime $authenticationFailureResetDate): void
    {
            $user->getConsecutiveAuthenticationFailureCounter()->willReturn($consecutiveAuthenticationFailureCounter);
            $user->getAuthenticationFailureResetDate()->willReturn($authenticationFailureResetDate);
            $user->getRoles()->willReturn([]);
            $user->getPassword()->willReturn('');
            $user->getSalt()->willReturn('');
        }

    private function initPassport(Passport $passport, UserBadge $badge, UserInterface $user): void
    {
            $passport->hasBadge(UserBadge::class)->willReturn(true);
            $passport->getBadge(UserBadge::class)->willReturn($badge);
            $badge->getUser()->willReturn($user);
        }

    private function getAuthenticationFailureResetDateFromNow(int $minutesBehind): \DateTime
    {
            return (new \DateTime())->modify("-{$minutesBehind} minute");
        }

    private function lockStateShouldBeUpdated(UserManager $userManager, UserInterface $user, int $expectedConsecutiveAuthenticationFailureCounter): void
    {
            $user->setConsecutiveAuthenticationFailureCounter($expectedConsecutiveAuthenticationFailureCounter)->shouldHaveBeenCalledOnce();
            $user->setAuthenticationFailureResetDate()->shouldNotBeCalled();
            $userManager->updateUser($user)->shouldHaveBeenCalled();
        }

    private function lockStateShouldBeReset(UserInterface $user, UserManager $userManager): void
    {
            $user->setConsecutiveAuthenticationFailureCounter(0)->shouldHaveBeenCalledOnce();
            $user->setAuthenticationFailureResetDate(null)->shouldHaveBeenCalled();
            $userManager->updateUser($user)->shouldHaveBeenCalled();
        }
}
