<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Connector;

use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use PHPUnit\Framework\TestCase;

class RoleWithPermissionsTest extends TestCase
{
    private RoleWithPermissions $sut;

    protected function setUp(): void
    {
        $this->sut = new RoleWithPermissions();
    }

}
