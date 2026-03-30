<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\EventSubscriber\Product\OnDelete;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnDelete\ComputeProductsAndAncestorsSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ComputeProductsAndAncestorsSubscriberTest extends TestCase
{
    private ComputeProductsAndAncestorsSubscriber $sut;

    protected function setUp(): void
    {
        $this->sut = new ComputeProductsAndAncestorsSubscriber();
    }

}
