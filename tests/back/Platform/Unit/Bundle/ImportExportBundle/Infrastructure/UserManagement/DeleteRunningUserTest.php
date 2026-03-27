<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Bundle\ImportExportBundle\Infrastructure\UserManagement;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\ResolveScheduledJobRunningUsername;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\UserManagement\DeleteRunningUser;
use Akeneo\UserManagement\ServiceApi\User\DeleteUserCommand;
use Akeneo\UserManagement\ServiceApi\User\DeleteUserHandlerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteRunningUserTest extends TestCase
{
    private DeleteUserHandlerInterface|MockObject $deleteUserHandler;
    private ResolveScheduledJobRunningUsername|MockObject $resolveScheduledJobRunningUsername;
    private DeleteRunningUser $sut;

    protected function setUp(): void
    {
        $this->deleteUserHandler = $this->createMock(DeleteUserHandlerInterface::class);
        $this->resolveScheduledJobRunningUsername = $this->createMock(ResolveScheduledJobRunningUsername::class);
        $this->sut = new DeleteRunningUser($this->deleteUserHandler, $this->resolveScheduledJobRunningUsername);
    }

    public function test_it_calls_delete_user_through_user_management_public_api(): void
    {
        $this->resolveScheduledJobRunningUsername->expects($this->once())->method('fromJobCode')->with('my_job_name')->willReturn('job_automated_my_job_name');
        $command = new DeleteUserCommand('job_automated_my_job_name', );
        $this->deleteUserHandler->expects($this->once())->method('handle')->with($command);
        $this->sut->execute('my_job_name');
    }
}
