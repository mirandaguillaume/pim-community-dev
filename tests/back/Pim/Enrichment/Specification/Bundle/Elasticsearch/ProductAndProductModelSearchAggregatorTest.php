<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Elasticsearch;

use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelSearchAggregator;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use PHPUnit\Framework\TestCase;

class ProductAndProductModelSearchAggregatorTest extends TestCase
{
    private ProductAndProductModelSearchAggregator $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductAndProductModelSearchAggregator();
    }

}
