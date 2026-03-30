<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Test\Acceptance\User;

use Akeneo\Test\Acceptance\User\InMemoryGroupRepository;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\Group;
use PHPUnit\Framework\TestCase;

class InMemoryGroupRepositoryTest extends TestCase
{
    private InMemoryGroupRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryGroupRepository();
    }

}
