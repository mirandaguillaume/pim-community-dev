<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Model;

use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    private Role $sut;

    protected function setUp(): void
    {
        $this->sut = new Role();
    }

}
