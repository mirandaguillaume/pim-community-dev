<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Test\Acceptance\User;

use Akeneo\Test\Acceptance\User\InMemoryRoleRepository;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use PHPUnit\Framework\TestCase;

class InMemoryRoleRepositoryTest extends TestCase
{
    private InMemoryRoleRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryRoleRepository();
    }

}
