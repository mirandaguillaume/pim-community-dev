<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\FamilyRepository;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class FamilyRepositoryTest extends TestCase
{
    private FamilyRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyRepository();
    }

}
