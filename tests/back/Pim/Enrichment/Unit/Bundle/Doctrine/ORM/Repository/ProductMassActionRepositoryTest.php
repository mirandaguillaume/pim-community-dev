<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductMassActionRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilder;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

class ProductMassActionRepositoryTest extends TestCase
{
    private ProductMassActionRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductMassActionRepository();
    }

}
