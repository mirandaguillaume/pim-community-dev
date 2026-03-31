<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\MassEditAction;

use Akeneo\Pim\Enrichment\Bundle\MassEditAction\OperationJobLauncher;
use Akeneo\Pim\Enrichment\Bundle\MassEditAction\Operation\BatchableOperationInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\SimpleJobLauncher;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class OperationJobLauncherTest extends TestCase
{
    private SimpleJobLauncher|MockObject $jobLauncher;
    private IdentifiableObjectRepositoryInterface|MockObject $jobInstanceRepo;
    private TokenStorageInterface|MockObject $tokenStorage;
    private OperationJobLauncher $sut;

    protected function setUp(): void
    {
        $this->jobLauncher = $this->createMock(SimpleJobLauncher::class);
        $this->jobInstanceRepo = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->sut = new OperationJobLauncher($this->jobLauncher, $this->jobInstanceRepo, $this->tokenStorage);
    }

    public function test_it_launches_a_background_process_from_an_operation(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $operation = $this->createMock(BatchableOperationInterface::class);
        $jobInstance = $this->createMock(JobInstance::class);

        $user = new User();
        $user->setUsername('julia');
        $operation->method('getJobInstanceCode')->willReturn('mass_classify');
        $this->jobInstanceRepo->method('findOneByIdentifier')->with('mass_classify')->willReturn($jobInstance);
        $operation->method('getBatchConfig')->willReturn([
                    'foo'  => 'bar',
                    'pomf' => 'thud'
                ]);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $token->method('getUser')->willReturn($user);
        $this->jobLauncher->method('launch')->with($jobInstance,
                    $user,
                    [
                        'foo'  => 'bar',
                        'pomf' => 'thud',
                        'users_to_notify' => ['julia']
                    ]);
        $this->sut->launch($operation);
    }

    public function test_it_throws_an_exception_if_no_job_instance_is_found(): void
    {
        $operation = $this->createMock(BatchableOperationInterface::class);

        $operation->method('getJobInstanceCode')->willReturn('mass_colorize');
        $this->jobInstanceRepo->method('findOneByIdentifier')->with('mass_colorize')->willReturn(null);
        $this->expectException(NotFoundResourceException::class);

        $this->expectExceptionMessage('No JobInstance found with code "mass_colorize"');
        $this->sut->launch($operation);
    }
}
