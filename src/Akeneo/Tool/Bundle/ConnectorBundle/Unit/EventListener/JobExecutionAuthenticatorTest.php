<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Tool\Bundle\ConnectorBundle\EventListener\JobExecutionAuthenticator;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class JobExecutionAuthenticatorTest extends TestCase
{
    private UserProviderInterface|MockObject $jobUserProvider;
    private UserProviderInterface|MockObject $uiUserProvider;
    private TokenStorageInterface|MockObject $tokenStorage;
    private JobExecutionAuthenticator $sut;

    protected function setUp(): void
    {
        $this->jobUserProvider = $this->createMock(UserProviderInterface::class);
        $this->uiUserProvider = $this->createMock(UserProviderInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->sut = new JobExecutionAuthenticator($this->jobUserProvider, $this->uiUserProvider, $this->tokenStorage);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(JobExecutionAuthenticator::class, $this->sut);
    }

    public function test_it_authenticates_user_with_token(): void
    {
        $event = $this->createMock(JobExecutionEvent::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);
        $user = $this->createMock(UserInterface::class);

        $event->method('getJobExecution')->willReturn($jobExecution);
        $jobExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobExecution->method('getUser')->willReturn('julia');
        $jobParameters->method('has')->with('is_user_authenticated')->willReturn(true);
        $jobParameters->method('get')->with('is_user_authenticated')->willReturn(true);
        $this->jobUserProvider->method('loadUserByIdentifier')->with('julia')->willThrowException(new UserNotFoundException(sprintf('User with username "%s" does not exist or is not a Job user.', 'julia')));
        $this->uiUserProvider->expects($this->once())->method('loadUserByIdentifier')->with('julia')->willReturn($user);
        $user->method('getRoles')->willReturn(['role']);
        $token  = new UsernamePasswordToken($user, 'main', ['role']);
        $this->tokenStorage->expects($this->once())->method('setToken')->with($token);
        $this->sut->authenticate($event);
    }

    public function test_it_does_not_authenticate_user_when_user_is_null(): void
    {
        $event = $this->createMock(JobExecutionEvent::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);
        $user = $this->createMock(UserInterface::class);

        $event->method('getJobExecution')->willReturn($jobExecution);
        $jobExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobExecution->method('getUser')->willReturn(null);
        $token  = new UsernamePasswordToken($user, 'main', ['role']);
        $this->tokenStorage->expects($this->never())->method('setToken')->with($token);
        $this->sut->authenticate($event);
    }

    public function test_it_does_authenticates_user_when_no_job_parameters(): void
    {
        $event = $this->createMock(JobExecutionEvent::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $user = $this->createMock(UserInterface::class);

        $event->method('getJobExecution')->willReturn($jobExecution);
        $jobExecution->method('getJobParameters')->willReturn(null);
        $jobExecution->method('getUser')->willReturn('julia');
        $token  = new UsernamePasswordToken($user, 'main', ['role']);
        $this->tokenStorage->expects($this->never())->method('setToken')->with($token);
        $this->sut->authenticate($event);
    }

    public function test_it_does_not_authenticates_user_when_it_is_not_configured_in_job_parameters(): void
    {
        $event = $this->createMock(JobExecutionEvent::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);
        $user = $this->createMock(UserInterface::class);

        $event->method('getJobExecution')->willReturn($jobExecution);
        $jobExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobExecution->method('getUser')->willReturn('julia');
        $jobParameters->method('has')->with('is_user_authenticated')->willReturn(false);
        $token  = new UsernamePasswordToken($user, 'main', ['role']);
        $this->tokenStorage->expects($this->never())->method('setToken')->with($token);
        $this->sut->authenticate($event);
    }

    public function test_it_does_not_authenticates_user_when_it_is_not_activated_in_job_parameters(): void
    {
        $event = $this->createMock(JobExecutionEvent::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);
        $user = $this->createMock(UserInterface::class);

        $event->method('getJobExecution')->willReturn($jobExecution);
        $jobExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobExecution->method('getUser')->willReturn('julia');
        $jobParameters->method('has')->with('is_user_authenticated')->willReturn(false);
        $jobParameters->method('get')->with('is_user_authenticated')->willReturn(false);
        $token  = new UsernamePasswordToken($user, 'main', ['role']);
        $this->tokenStorage->expects($this->never())->method('setToken')->with($token);
        $this->sut->authenticate($event);
    }

    public function test_it_throws_exception_if_username_is_not_found(): void
    {
        $event = $this->createMock(JobExecutionEvent::class);
        $jobExecution = $this->createMock(JobExecution::class);
        $jobParameters = $this->createMock(JobParameters::class);
        $user = $this->createMock(UserInterface::class);

        $event->method('getJobExecution')->willReturn($jobExecution);
        $jobExecution->method('getJobParameters')->willReturn($jobParameters);
        $jobExecution->method('getUser')->willReturn('julia');
        $jobParameters->method('has')->with('is_user_authenticated')->willReturn(true);
        $jobParameters->method('get')->with('is_user_authenticated')->willReturn(true);
        $this->uiUserProvider->method('loadUserByIdentifier')->with('julia')->willThrowException(UserNotFoundException::class);
        $this->jobUserProvider->method('loadUserByIdentifier')->with('julia')->willThrowException(UserNotFoundException::class);
        $token  = new UsernamePasswordToken($user, 'main', ['role']);
        $this->tokenStorage->expects($this->never())->method('setToken')->with($token);
        $this->expectException(UserNotFoundException::class);
        $this->sut->authenticate($event);
    }
}
