<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Remover;

use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountEntityWithFamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Remover\FamilyVariantRemover;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FamilyVariantRemoverTest extends TestCase
{
    private FamilyVariantRemover $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyVariantRemover();
    }

}
