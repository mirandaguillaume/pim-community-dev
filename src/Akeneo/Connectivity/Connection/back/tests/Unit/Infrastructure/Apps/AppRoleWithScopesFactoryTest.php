<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps;

use Akeneo\Connectivity\Connection\Application\Apps\AppRoleWithScopesFactoryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\AppRoleWithScopesFactory;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppRoleWithScopesFactoryTest extends TestCase
{
    private const string APP_LABEL = 'App prototype';

    /**
     * The scopes the app asks for in the "activate an App" flow, and the API ACLs they map to.
     */
    private const array SCOPE_ACLS = [
        'read_products' => ['pim_api_product_list'],
        'write_products' => ['pim_api_product_edit'],
        'delete_products' => ['pim_api_product_remove'],
    ];

    private ScopeMapperInterface|MockObject $scopeMapper;
    private SimpleFactoryInterface|MockObject $roleFactory;
    private RoleInterface|MockObject $role;
    private ObjectManager|MockObject $objectManager;
    private EventDispatcherInterface|MockObject $eventDispatcher;
    private AclPrivilegeRepository|MockObject $privilegeRepository;
    private AclManager|MockObject $aclManager;
    private AppRoleWithScopesFactory $sut;

    protected function setUp(): void
    {
        $this->scopeMapper = $this->createMock(ScopeMapperInterface::class);
        $this->scopeMapper->method('getScopes')->willReturn(\array_keys(self::SCOPE_ACLS));
        $this->scopeMapper->method('getLowerHierarchyScopes')->willReturn([]);
        $this->scopeMapper->method('getAcls')->willReturnCallback(
            static fn (string $scope): array => self::SCOPE_ACLS[$scope]
        );

        $this->role = $this->createMock(RoleInterface::class);
        $this->roleFactory = $this->createMock(SimpleFactoryInterface::class);
        $this->roleFactory->method('create')->willReturn($this->role);

        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->eventDispatcher->method('dispatch')->willReturnArgument(0);
        $this->privilegeRepository = $this->createMock(AclPrivilegeRepository::class);
        $this->aclManager = $this->createMock(AclManager::class);
        $this->aclManager->method('getSid')->willReturn(new RoleSecurityIdentity('ROLE_APP'));
        $this->aclManager->method('getPrivilegeRepository')->willReturn($this->privilegeRepository);

        // ScopeMapperRegistry and RoleWithPermissionsSaver are final: they cannot be doubled, so the real
        // collaborators are built on top of doubled dependencies.
        $this->sut = new AppRoleWithScopesFactory(
            new ScopeMapperRegistry([$this->scopeMapper]),
            $this->roleFactory,
            new RoleWithPermissionsSaver($this->objectManager, $this->eventDispatcher, $this->aclManager)
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AppRoleWithScopesFactory::class, $this->sut);
        $this->assertInstanceOf(AppRoleWithScopesFactoryInterface::class, $this->sut);
    }

    public function test_it_creates_a_role_of_type_app_labelled_after_the_app(): void
    {
        $this->givenTheExistingPrivileges();

        $this->role->expects($this->once())->method('setLabel')->with(self::APP_LABEL);
        $this->role->expects($this->once())->method('setType')->with('app');

        $this->assertSame($this->role, $this->sut->createRole(self::APP_LABEL, ['read_products']));
    }

    public function test_it_persists_the_created_role(): void
    {
        $this->givenTheExistingPrivileges();

        $this->objectManager->expects($this->once())->method('persist')->with($this->role);
        $this->objectManager->expects($this->once())->method('flush');

        $this->sut->createRole(self::APP_LABEL, ['read_products']);
    }

    public function test_it_gives_the_role_a_random_code(): void
    {
        $this->givenTheExistingPrivileges();

        $roleCode = null;
        $this->role->expects($this->once())->method('setRole')->willReturnCallback(
            function (?string $code) use (&$roleCode): void {
                $roleCode = $code;
            }
        );

        $this->sut->createRole(self::APP_LABEL, ['read_products']);

        $this->assertIsString($roleCode);
        $this->assertMatchesRegularExpression('/^[0-9a-z]+$/', $roleCode);
    }

    public function test_it_gives_a_different_code_to_each_created_role(): void
    {
        $this->givenTheExistingPrivileges();

        $roleCodes = [];
        $this->role->expects($this->exactly(2))->method('setRole')->willReturnCallback(
            function (?string $code) use (&$roleCodes): void {
                $roleCodes[] = $code;
            }
        );

        $this->sut->createRole(self::APP_LABEL, ['read_products']);
        $this->sut->createRole('Another app', ['read_products']);

        $this->assertCount(2, $roleCodes);
        $this->assertNotSame($roleCodes[0], $roleCodes[1]);
    }

    /**
     * Unitary counterpart of the "my connected app has the following ACLs" step of activate_an_app.feature.
     */
    public function test_it_grants_the_overall_api_access_and_the_acls_of_the_authorized_scopes(): void
    {
        $overallAccess = $this->aclPrivilege('action:pim_api_overall_access');
        $productList = $this->aclPrivilege('action:pim_api_product_list');
        $productEdit = $this->aclPrivilege('action:pim_api_product_edit');
        $productRemove = $this->aclPrivilege('action:pim_api_product_remove');
        $attributeList = $this->aclPrivilege('action:pim_api_attribute_list');

        $this->givenTheExistingPrivileges($overallAccess, $productList, $productEdit, $productRemove, $attributeList);
        $this->privilegeRepository->expects($this->once())->method('savePrivileges');

        $this->sut->createRole(self::APP_LABEL, ['read_products', 'write_products', 'delete_products']);

        $this->assertSame(AccessLevel::SYSTEM_LEVEL, $this->grantedLevelOf($overallAccess));
        $this->assertSame(AccessLevel::SYSTEM_LEVEL, $this->grantedLevelOf($productList));
        $this->assertSame(AccessLevel::SYSTEM_LEVEL, $this->grantedLevelOf($productEdit));
        $this->assertSame(AccessLevel::SYSTEM_LEVEL, $this->grantedLevelOf($productRemove));
        $this->assertSame(AccessLevel::NONE_LEVEL, $this->grantedLevelOf($attributeList));
    }

    public function test_it_grants_only_the_overall_api_access_when_no_scope_is_authorized(): void
    {
        $overallAccess = $this->aclPrivilege('action:pim_api_overall_access');
        $productList = $this->aclPrivilege('action:pim_api_product_list');

        $this->givenTheExistingPrivileges($overallAccess, $productList);
        $this->privilegeRepository->expects($this->once())->method('savePrivileges');

        $this->sut->createRole(self::APP_LABEL, []);

        $this->assertSame(AccessLevel::SYSTEM_LEVEL, $this->grantedLevelOf($overallAccess));
        $this->assertSame(AccessLevel::NONE_LEVEL, $this->grantedLevelOf($productList));
    }

    public function test_it_does_not_grant_the_acls_of_a_scope_that_was_not_authorized(): void
    {
        $productList = $this->aclPrivilege('action:pim_api_product_list');
        $productEdit = $this->aclPrivilege('action:pim_api_product_edit');
        $productRemove = $this->aclPrivilege('action:pim_api_product_remove');

        $this->givenTheExistingPrivileges($productList, $productEdit, $productRemove);

        $this->sut->createRole(self::APP_LABEL, ['read_products']);

        $this->assertSame(AccessLevel::SYSTEM_LEVEL, $this->grantedLevelOf($productList));
        $this->assertSame(AccessLevel::NONE_LEVEL, $this->grantedLevelOf($productEdit));
        $this->assertSame(AccessLevel::NONE_LEVEL, $this->grantedLevelOf($productRemove));
    }

    private function givenTheExistingPrivileges(AclPrivilege ...$privileges): void
    {
        $this->privilegeRepository->method('getPrivileges')->willReturn(new ArrayCollection($privileges));
    }

    private function aclPrivilege(string $identity): AclPrivilege
    {
        $privilege = new AclPrivilege();
        $privilege->setIdentity(new AclPrivilegeIdentity($identity));
        $privilege->addPermission(new AclPermission('EXECUTE', AccessLevel::NONE_LEVEL));

        return $privilege;
    }

    private function grantedLevelOf(AclPrivilege $privilege): int
    {
        return $privilege->getPermissions()['EXECUTE']->getAccessLevel();
    }
}
