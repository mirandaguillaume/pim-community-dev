<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchBundle\Remover;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\UserManagement\DeleteRunningUser;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\RemovableObjectRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use spec\Akeneo\Tool\Bundle\BatchBundle\Remover\JobInstanceRemover;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class JobInstanceRemoverTest extends TestCase
{
    private RemovableObjectRepositoryInterface|MockObject $jobInstanceRepository;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private DeleteRunningUser|MockObject $deleteRunningUser;
    private LoggerInterface|MockObject $logger;
    private JobInstanceRemover $sut;

    protected function setUp(): void
    {
        $this->jobInstanceRepository = $this->createMock(RemovableObjectRepositoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->deleteRunningUser = $this->createMock(DeleteRunningUser::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->sut = new JobInstanceRemover(
            $this->jobInstanceRepository,
            $this->eventDispatcher,
            $this->deleteRunningUser,
            $this->logger,
        );
        $this->eventDispatcher->method('dispatch')->with($this->anything(), $this->isType('string'))->willReturn($this->isType('object'));
    }

    public function test_it_is_a_remover(): void
    {
        $this->assertInstanceOf(RemoverInterface::class, $this->sut);
        $this->assertInstanceOf(BulkRemoverInterface::class, $this->sut);
    }

    public function test_it_removes_the_job_instance(): void
    {
        $jobInstance = $this->createMock(JobInstance::class);

        $jobInstance->method('getId')->willReturn(1);
        $jobInstance->method('isScheduled')->willReturn(false);
        $jobInstanceCode = 'my_job';
        $jobInstance->method('getCode')->willReturn($jobInstanceCode);
        $this->jobInstanceRepository->expects($this->once())->method('remove')->with($jobInstanceCode);
        $this->sut->remove($jobInstance);
    }

    public function test_it_removes_the_objects(): void
    {
        $jobInstance1 = $this->createMock(JobInstance::class);
        $jobInstance2 = $this->createMock(JobInstance::class);

        $jobInstance1->method('getId')->willReturn(1);
        $jobInstanceCode1 = 'my_job1';
        $jobInstance1->method('getCode')->willReturn($jobInstanceCode1);
        $jobInstance2->method('getId')->willReturn(2);
        $jobInstanceCode2 = 'my_job2';
        $jobInstance2->method('getCode')->willReturn($jobInstanceCode2);
        $this->jobInstanceRepository->expects($this->once())->method('remove')->with($jobInstanceCode1);
        $this->jobInstanceRepository->expects($this->once())->method('remove')->with($jobInstanceCode2);
        $this->sut->removeAll([$jobInstance1, $jobInstance2]);
    }

    public function test_it_removes_the_running_user(): void
    {
        $jobInstance = $this->createMock(JobInstance::class);

        $jobInstance->method('getId')->willReturn(1);
        $jobInstance->method('isScheduled')->willReturn(true);
        $jobInstanceCode = 'my_job';
        $jobInstance->method('getCode')->willReturn($jobInstanceCode);
        $this->deleteRunningUser->expects($this->once())->method('execute')->with($jobInstanceCode);
        $this->sut->remove($jobInstance);
    }

    public function test_it_throws_exception_when_remove_anything_else_than_a_job_instance(): void
    {
        $anythingElse = new \stdClass();
        $exception = new \InvalidArgumentException(
            sprintf(
                'Expects a "%s", "%s" provided.',
                JobInstance::class,
                $anythingElse::class
            )
        );
        $this->expectException($exception);
        $this->sut->remove($anythingElse);
        $this->expectException($exception);
        $this->sut->removeAll([$anythingElse, $anythingElse]);
    }
}
