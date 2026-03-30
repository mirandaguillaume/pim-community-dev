<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Updater\GroupUpdater;
use PHPUnit\Framework\TestCase;

class GroupUpdaterTest extends TestCase
{
    private GroupUpdater $sut;

    protected function setUp(): void
    {
        $this->sut = new GroupUpdater();
    }

}
