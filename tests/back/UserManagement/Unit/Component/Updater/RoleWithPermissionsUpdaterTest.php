<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Updater;

use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Updater\RoleWithPermissionsUpdater;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Webmozart\Assert\Assert;

class RoleWithPermissionsUpdaterTest extends TestCase
{
    private RoleWithPermissionsUpdater $sut;

    protected function setUp(): void
    {
        $this->sut = new RoleWithPermissionsUpdater();
    }

}
