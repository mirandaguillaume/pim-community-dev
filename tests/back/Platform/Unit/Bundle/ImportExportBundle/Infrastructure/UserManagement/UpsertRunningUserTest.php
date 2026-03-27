<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Bundle\ImportExportBundle\Infrastructure\UserManagement;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\ResolveScheduledJobRunningUsername;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\UserManagement\UpsertRunningUser;
use Akeneo\UserManagement\ServiceApi\UserRole\ListUserRoleInterface;
use Akeneo\UserManagement\ServiceApi\UserRole\UserRole;
use Akeneo\UserManagement\ServiceApi\User\UpsertUserCommand;
use Akeneo\UserManagement\ServiceApi\User\UpsertUserHandlerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpsertRunningUserTest extends TestCase
{
    private UpsertUserHandlerInterface|MockObject $upsertUserHandler;
    private ListUserRoleInterface|MockObject $listUserRole;
    private ResolveScheduledJobRunningUsername|MockObject $resolveScheduledJobRunningUsername;
    private UpsertRunningUser $sut;

    protected function setUp(): void
    {
        $this->upsertUserHandler = $this->createMock(UpsertUserHandlerInterface::class);
        $this->listUserRole = $this->createMock(ListUserRoleInterface::class);
        $this->resolveScheduledJobRunningUsername = $this->createMock(ResolveScheduledJobRunningUsername::class);
        $this->sut = new UpsertRunningUser($this->upsertUserHandler, $this->listUserRole, $this->resolveScheduledJobRunningUsername);
    }

    public function test_it_calls_upsert_user_through_user_management_public_api(): void
    {
        $administratorRole = $this->createMock(UserRole::class);
        $userRole = $this->createMock(UserRole::class);

        $this->resolveScheduledJobRunningUsername->expects($this->once())->method('fromJobCode')->with('my_job_name')->willReturn('job_automated_my_job_name');
        $administratorRole->method('getRole')->willReturn('ROLE_ADMINISTRATOR');
        $userRole->method('getRole')->willReturn('ROLE_USER');
        $this->listUserRole->method('all')->willReturn([$administratorRole, $userRole]);
        $command = UpsertUserCommand::job(
            'job_automated_my_job_name',
            'fakepassword',
            'job_automated_my_job_name@example.com',
            'my_job_name',
            'Automated Job',
            ['ROLE_ADMINISTRATOR', 'ROLE_USER'],
            ['IT Support'],
        );
        $this->upsertUserHandler->expects($this->once())->method('handle')->with($command);
        $this->sut->execute('my_job_name', ['IT Support']);
    }
}
