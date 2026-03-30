<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ConnectorBundle\Launcher;

use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\ConnectorBundle\Launcher\AuthenticatedJobLauncher;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticatedJobLauncherTest extends TestCase
{
    private JobLauncherInterface|MockObject $jobLauncher;
    private AuthenticatedJobLauncher $sut;

    protected function setUp(): void
    {
        $this->jobLauncher = $this->createMock(JobLauncherInterface::class);
        $this->sut = new AuthenticatedJobLauncher($this->jobLauncher);
    }

    public function test_it_is_a_job_launcher(): void
    {
        $this->assertInstanceOf(JobLauncherInterface::class, $this->sut);
    }

    public function test_it_should_force_authentication_in_configuration(): void
    {
        $jobInstance = $this->createMock(JobInstance::class);
        $user = $this->createMock(UserInterface::class);

        $this->jobLauncher->method('launch')->with($jobInstance, $user, ['filePath' => '/tmp', 'is_user_authenticated' => true]);
        $this->sut->launch($jobInstance, $user, ['filePath' => '/tmp']);
    }
}
