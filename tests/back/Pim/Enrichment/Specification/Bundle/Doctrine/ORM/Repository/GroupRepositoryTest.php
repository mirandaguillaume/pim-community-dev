<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class GroupRepositoryTest extends TestCase
{
    private GroupRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new GroupRepository();
    }

}
