<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\Product\OnSave;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave\ComputeProductsAndAncestorsSubscriber;
use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\GenericEvent;

class ComputeProductsAndAncestorsSubscriberTest extends TestCase
{
    private ComputeProductsAndAncestorsSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new ComputeProductsAndAncestorsSubscriber();
    }

}
