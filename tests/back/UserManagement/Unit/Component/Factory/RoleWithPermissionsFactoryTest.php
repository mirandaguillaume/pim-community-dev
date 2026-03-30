<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Factory;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactory;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Factory\RoleWithPermissionsFactory;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use PHPUnit\Framework\TestCase;

class RoleWithPermissionsFactoryTest extends TestCase
{
    private RoleWithPermissionsFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new RoleWithPermissionsFactory();
    }

}
