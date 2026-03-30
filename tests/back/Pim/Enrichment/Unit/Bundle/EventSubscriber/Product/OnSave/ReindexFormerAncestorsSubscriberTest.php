<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\Product\OnSave;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave\ReindexFormerAncestorsSubscriber;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetAncestorAndDescendantProductModelCodes;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\Event\ParentHasBeenRemovedFromVariantProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\GenericEvent;

class ReindexFormerAncestorsSubscriberTest extends TestCase
{
    private ReindexFormerAncestorsSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new ReindexFormerAncestorsSubscriber();
    }

}
