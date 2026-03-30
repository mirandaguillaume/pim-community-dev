<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Factory;

use Akeneo\Category\Infrastructure\Component\Classification\Model\Category;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Platform\Bundle\UIBundle\UiLocaleProvider;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Factory\DefaultProperty;
use Akeneo\UserManagement\Component\Factory\UserFactory;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use PHPUnit\Framework\TestCase;

class UserFactoryTest extends TestCase
{
    private UserFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new UserFactory();
    }

}
