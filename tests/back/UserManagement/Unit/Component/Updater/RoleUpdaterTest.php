<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Updater\RoleUpdater;
use PHPUnit\Framework\TestCase;

class RoleUpdaterTest extends TestCase
{
    private RoleUpdater $sut;

    protected function setUp(): void
    {
        $this->sut = new RoleUpdater();
    }

}
