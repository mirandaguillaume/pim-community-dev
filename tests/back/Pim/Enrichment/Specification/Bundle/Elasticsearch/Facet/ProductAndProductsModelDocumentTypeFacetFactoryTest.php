<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Elasticsearch\Facet;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ElasticsearchResult;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet\Facet;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet\ProductAndProductsModelDocumentTypeFacetFactory;
use PHPUnit\Framework\TestCase;

class ProductAndProductsModelDocumentTypeFacetFactoryTest extends TestCase
{
    private ProductAndProductsModelDocumentTypeFacetFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductAndProductsModelDocumentTypeFacetFactory();
    }

}
