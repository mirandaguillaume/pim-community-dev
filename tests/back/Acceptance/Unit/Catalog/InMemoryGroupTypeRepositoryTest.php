<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Akeneo\Test\Acceptance\Catalog;

use Akeneo\Pim\Structure\Component\Model\GroupType;
use Akeneo\Pim\Structure\Component\Repository\GroupTypeRepositoryInterface;
use Akeneo\Test\Acceptance\Catalog\InMemoryGroupTypeRepository;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PHPUnit\Framework\TestCase;

class InMemoryGroupTypeRepositoryTest extends TestCase
{
    private InMemoryGroupTypeRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryGroupTypeRepository();
    }

}
