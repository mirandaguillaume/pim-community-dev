<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Service\User;

use Akeneo\Connectivity\Connection\Application\Settings\Service\UpdateUserPermissionsInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Infrastructure\Service\User\UpdateUserPermissions;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateUserPermissionsTest extends TestCase
{
    private UserManager|MockObject $userManager;
    private RoleRepository|MockObject $roleRepository;
    private GroupRepository|MockObject $groupRepository;
    private ObjectUpdaterInterface|MockObject $userUpdater;
    private UpdateUserPermissions $sut;

    protected function setUp(): void
    {
        $this->userManager = $this->createMock(UserManager::class);
        $this->roleRepository = $this->createMock(RoleRepository::class);
        $this->groupRepository = $this->createMock(GroupRepository::class);
        $this->userUpdater = $this->createMock(ObjectUpdaterInterface::class);
        $this->sut = new UpdateUserPermissions($this->userManager, $this->roleRepository, $this->groupRepository, $this->userUpdater);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(UpdateUserPermissions::class, $this->sut);
        $this->assertInstanceOf(UpdateUserPermissionsInterface::class, $this->sut);
    }

    public function test_it_updates_user_permissions(): void
    {
        $userId = new UserId(1234);
        $user = new User();
        $roleId = 321;
        $role = new Role('ROLE_USER');
        $groupId = 456;
        $group = new Group('API');
        $this->userManager->method('findUserBy')->with(['id' => $userId->id()])->willReturn($user);
        $this->roleRepository->method('find')->with($roleId)->willReturn($role);
        $this->groupRepository->method('find')->with($groupId)->willReturn($group);
        $this->userUpdater->expects($this->once())->method('update')->with($user, ['roles' => ['ROLE_USER'], 'groups' => ['API']]);
        $this->userManager->expects($this->once())->method('updateUser')->with($this->anything());
        $this->sut->execute($userId, $roleId, $groupId);
    }

    public function test_it_throws_an_exception_if_user_not_found(): void
    {
        $userId = new UserId(1234);
        $roleId = 321;
        $groupId = 456;
        $this->userManager->method('findUserBy')->with(['id' => $userId->id()])->willReturn(null);
        $this->userUpdater->expects($this->never())->method('update');
        $this->userManager->expects($this->never())->method('updateUser')->with($this->anything());
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('User with id "1234" not found.');
        $this->sut->execute($userId, $roleId, $groupId);
    }

    public function test_it_throws_an_exception_if_role_not_found(): void
    {
        $userId = new UserId(1234);
        $user = new User();
        $roleId = 321;
        $groupId = 456;
        $this->userManager->method('findUserBy')->with(['id' => $userId->id()])->willReturn($user);
        $this->roleRepository->method('find')->with($roleId)->willReturn(null);
        $this->userUpdater->expects($this->never())->method('update');
        $this->userManager->expects($this->never())->method('updateUser')->with($this->anything());
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('Role with id "321" not found.');
        $this->sut->execute($userId, $roleId, $groupId);
    }

    public function test_it_throws_an_exception_if_group_not_found(): void
    {
        $userId = new UserId(1234);
        $user = new User();
        $roleId = 321;
        $role = new Role();
        $groupId = 456;
        $this->userManager->method('findUserBy')->with(['id' => $userId->id()])->willReturn($user);
        $this->roleRepository->method('find')->with($roleId)->willReturn($role);
        $this->groupRepository->method('find')->with($groupId)->willReturn(null);
        $this->userUpdater->expects($this->never())->method('update');
        $this->userManager->expects($this->never())->method('updateUser')->with($this->anything());
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('Group with id "456" not found.');
        $this->sut->execute($userId, $roleId, $groupId);
    }
}
