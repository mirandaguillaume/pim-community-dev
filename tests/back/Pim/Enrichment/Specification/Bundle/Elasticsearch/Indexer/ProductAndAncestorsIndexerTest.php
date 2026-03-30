<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetAncestorProductModelCodes;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ProductAndAncestorsIndexerTest extends TestCase
{
    private ProductAndAncestorsIndexer $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductAndAncestorsIndexer();
    }

}
