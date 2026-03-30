<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Test\Acceptance\User;

use Akeneo\Test\Acceptance\User\InMemoryUserRepository;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;

class InMemoryUserRepositoryTest extends TestCase
{
    private InMemoryUserRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryUserRepository();
    }

}
