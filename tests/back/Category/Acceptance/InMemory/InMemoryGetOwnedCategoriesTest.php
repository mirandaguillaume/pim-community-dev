<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Acceptance\InMemory;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Test\Acceptance\User\InMemoryUserRepository;
use Akeneo\Test\Category\Acceptance\InMemory\InMemoryGetOwnedCategories;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\TestCase;

class InMemoryGetOwnedCategoriesTest extends TestCase
{
    private InMemoryGetOwnedCategories $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryGetOwnedCategories();
    }

}
