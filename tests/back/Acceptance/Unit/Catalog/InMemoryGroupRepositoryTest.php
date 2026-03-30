<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Test\Acceptance\Catalog;

use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Akeneo\Test\Acceptance\Catalog\InMemoryGroupRepository;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PHPUnit\Framework\TestCase;

class InMemoryGroupRepositoryTest extends TestCase
{
    private InMemoryGroupRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryGroupRepository();
    }

}
