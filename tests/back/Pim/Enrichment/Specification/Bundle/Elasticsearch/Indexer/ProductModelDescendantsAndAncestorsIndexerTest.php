<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetAncestorAndDescendantProductModelCodes;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetDescendantVariantProductUuids;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ProductModelDescendantsAndAncestorsIndexerTest extends TestCase
{
    private ProductModelDescendantsAndAncestorsIndexer $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductModelDescendantsAndAncestorsIndexer();
    }

}
