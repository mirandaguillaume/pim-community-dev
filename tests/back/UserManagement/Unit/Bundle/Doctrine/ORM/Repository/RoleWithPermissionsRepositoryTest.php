<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Bundle\Doctrine\ORM\Repository;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

class RoleWithPermissionsRepositoryTest extends TestCase
{
    private RoleWithPermissionsRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new RoleWithPermissionsRepository();
    }

    private function createAclprivilege(string $privilegeId, bool $isGranted): AclPrivilege
    {
            $privilege = new AclPrivilege();
            [$extensionKey, $privilegeName] = explode(':', $privilegeId);
    
            $privilege->setIdentity(new AclPrivilegeIdentity($privilegeId, $privilegeName));
            $privilege->setExtensionKey($extensionKey);
            $privilege->addPermission(
                new AclPermission(
                    'EXECUTE',
                    $isGranted ? AccessLevel::SYSTEM_LEVEL : AccessLevel::NONE_LEVEL
                )
            );
    
            return $privilege;
        }
}
