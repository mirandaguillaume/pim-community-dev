<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Component\Model;

use Akeneo\Category\Infrastructure\Component\Classification\Model\Category;
use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\InMemoryUser;

class UserTest extends TestCase
{
    private User $sut;

    protected function setUp(): void
    {
        $this->sut = new User();
    }

}
