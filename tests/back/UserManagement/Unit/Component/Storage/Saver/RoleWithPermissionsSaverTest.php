<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Storage\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
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
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

class RoleWithPermissionsSaverTest extends TestCase
{
    private RoleWithPermissionsSaver $sut;

    protected function setUp(): void
    {
        $this->sut = new RoleWithPermissionsSaver();
    }

    private function createPrivilege(string $id, bool $isGranted): AclPrivilege
    {
            $privilege = new AclPrivilege();
            $privilege->setExtensionKey('action');
            $privilege->setIdentity(new AclPrivilegeIdentity($id));
            $privilege->addPermission(
                new AclPermission('EXECUTE', $isGranted ? AccessLevel::SYSTEM_LEVEL : AccessLevel::NONE_LEVEL)
            );
    
            return $privilege;
        }
}
