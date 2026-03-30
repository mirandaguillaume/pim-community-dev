<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\GroupFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;
use Akeneo\Pim\Structure\Component\Repository\GroupTypeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use PHPUnit\Framework\TestCase;

class GroupFactoryTest extends TestCase
{
    private GroupFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new GroupFactory();
    }

}
