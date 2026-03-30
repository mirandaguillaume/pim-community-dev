<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelSearchAggregator;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Bundle\ProductQueryBuilder\ProductAndProductModelQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PHPUnit\Framework\TestCase;

class ProductAndProductModelQueryBuilderTest extends TestCase
{
    private ProductAndProductModelQueryBuilder $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductAndProductModelQueryBuilder();
    }

}
