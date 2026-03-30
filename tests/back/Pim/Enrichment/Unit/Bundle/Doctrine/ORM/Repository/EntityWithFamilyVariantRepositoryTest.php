<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\EntityWithFamilyVariantRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\EntityWithFamilyVariantRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\VariantProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use PHPUnit\Framework\TestCase;

class EntityWithFamilyVariantRepositoryTest extends TestCase
{
    private EntityWithFamilyVariantRepository $sut;

    protected function setUp(): void
    {
        $this->sut = new EntityWithFamilyVariantRepository();
    }

}
