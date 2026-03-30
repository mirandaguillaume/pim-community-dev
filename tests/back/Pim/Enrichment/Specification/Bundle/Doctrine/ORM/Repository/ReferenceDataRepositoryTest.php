<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Doctrine\ORM\Repository;

use Acme\Bundle\AppBundle\Entity\Color;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ReferenceDataRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class ReferenceDataRepositoryTest extends TestCase
{
    private ReferenceDataRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new ReferenceDataRepository();
    }

}
