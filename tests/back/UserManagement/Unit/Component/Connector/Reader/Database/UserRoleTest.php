<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Connector\Reader\Database;

use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\UserManagement\Component\Connector\Reader\Database\UserRole;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    private UserRole $sut;

    protected function setUp(): void
    {
        $this->sut = new UserRole();
    }

}
