<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Installer\Unit\Infrastructure\FilesystemsPurger;

use Akeneo\Platform\Installer\Infrastructure\FilesystemsPurger\FilesystemsPurger;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class FilesystemsPurgerTest extends TestCase
{
    private JobLauncherInterface|MockObject $jobLauncher;
    private IdentifiableObjectRepositoryInterface|MockObject $jobInstanceRepository;
    private TokenStorageInterface|MockObject $tokenStorage;
    private FilesystemsPurger $sut;

    protected function setUp(): void
    {
        $this->jobLauncher = $this->createMock(JobLauncherInterface::class);
        $this->jobInstanceRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->sut = new FilesystemsPurger($this->jobLauncher, $this->jobInstanceRepository, $this->tokenStorage);
    }

    public function test_it_launch_purge_filesystems_job(): void
    {
        $purgeFilesystemsJobInstance = $this->createMock(JobInstance::class);
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);

        $this->jobInstanceRepository->method('findOneByIdentifier')->with('purge_filesystems')->willReturn($purgeFilesystemsJobInstance);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $this->jobLauncher->expects($this->once())->method('launch')->with($purgeFilesystemsJobInstance, $user);
        $this->sut->execute();
    }
}
