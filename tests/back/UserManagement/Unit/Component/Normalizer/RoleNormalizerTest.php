<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Normalizer;

use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Normalizer\RoleNormalizer;
use Oro\Bundle\SecurityBundle\Acl\Extension\AclExtensionInterface;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Oro\Bundle\SecurityBundle\Metadata\ActionMetadata;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RoleNormalizerTest extends TestCase
{
    private RoleNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new RoleNormalizer();
    }

}
