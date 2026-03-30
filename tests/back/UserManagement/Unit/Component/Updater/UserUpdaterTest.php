<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Updater;

use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Akeneo\UserManagement\Component\Updater\UserUpdater;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class UserUpdaterTest extends TestCase
{
    private UserUpdater $sut;

    protected function setUp(): void
    {
        $this->sut = new UserUpdater();
    }

}
