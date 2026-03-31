<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Service;

use Akeneo\Connectivity\Connection\Application\Apps\Security\ScopeMapperRegistryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetConnectedAppRoleIdentifierQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Service\UpdateConnectedAppRoleWithScopes;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateConnectedAppRoleWithScopesTest extends TestCase
{
    private GetConnectedAppRoleIdentifierQueryInterface|MockObject $getConnectedAppRoleIdentifierQuery;
    private RoleRepositoryInterface|MockObject $roleRepository;
    private ScopeMapperRegistryInterface|MockObject $scopeMapperRegistry;
    private BulkSaverInterface|MockObject $roleWithPermissionsSaver;
    private RoleInterface|MockObject $role;
    private UpdateConnectedAppRoleWithScopes $sut;

    protected function setUp(): void
    {
        $this->getConnectedAppRoleIdentifierQuery = $this->createMock(GetConnectedAppRoleIdentifierQueryInterface::class);
        $this->roleRepository = $this->createMock(RoleRepositoryInterface::class);
        $this->scopeMapperRegistry = $this->createMock(ScopeMapperRegistryInterface::class);
        $this->roleWithPermissionsSaver = $this->createMock(BulkSaverInterface::class);
        $this->role = $this->createMock(RoleInterface::class);
        $this->sut = new UpdateConnectedAppRoleWithScopes(
            $this->getConnectedAppRoleIdentifierQuery,
            $this->roleRepository,
            $this->scopeMapperRegistry,
            $this->roleWithPermissionsSaver,
        );
    }

    public function test_it_updates_connected_app_role_with_new_acl_given_scopes(): void
    {
        $this->getConnectedAppRoleIdentifierQuery->method('execute')->with('connected_app_id')->willReturn('ROLE_CONNECTED_APP');
        $this->roleRepository->method('findOneByIdentifier')->with('ROLE_CONNECTED_APP')->willReturn($this->role);
        $this->scopeMapperRegistry->method('getAllScopes')->willReturn(['scopeA', 'scopeB', 'scopeC', 'scopeD']);
        $this->scopeMapperRegistry->method('getAcls')->willReturnCallback(function (array $scopes) {
            if ($scopes === ['scopeA', 'scopeB', 'scopeC', 'scopeD']) {
                return ['some_acl_1', 'some_acl_2', 'some_acl_3', 'some_acl_4'];
            }
            if ($scopes === ['scopeA', 'scopeB', 'scopeC']) {
                return ['some_acl_1', 'some_acl_2', 'some_acl_3'];
            }
            return [];
        });

        $roleWithPermissions = RoleWithPermissions::createFromRoleAndPermissions($this->role, [
                    'action:pim_api_overall_access' => true,
                    'action:some_acl_1' => true,
                    'action:some_acl_2' => true,
                    'action:some_acl_3' => true,
                    'action:some_acl_4' => false,
                ]);
        $this->roleWithPermissionsSaver->expects($this->once())->method('saveAll')->with([$roleWithPermissions]);
        $this->sut->execute('connected_app_id', ['scopeA', 'scopeB', 'scopeC']);
    }

    public function test_it_throws_an_exception_when_no_role_identifier_is_found(): void
    {
        $this->getConnectedAppRoleIdentifierQuery->method('execute')->with('connected_app_id')->willReturn(null);
        $this->expectException(\LogicException::class);
        $this->sut->execute('connected_app_id', ['scopeA', 'scopeB', 'scopeC']);
    }

    public function test_it_throws_an_exception_when_no_role_entity_is_found(): void
    {
        $this->getConnectedAppRoleIdentifierQuery->method('execute')->with('connected_app_id')->willReturn('ROLE_CONNECTED_APP');
        $this->roleRepository->method('findOneByIdentifier')->with('ROLE_CONNECTED_APP')->willReturn(null);
        $this->expectException(\LogicException::class);
        $this->sut->execute('connected_app_id', ['scopeA', 'scopeB', 'scopeC']);
    }
}
