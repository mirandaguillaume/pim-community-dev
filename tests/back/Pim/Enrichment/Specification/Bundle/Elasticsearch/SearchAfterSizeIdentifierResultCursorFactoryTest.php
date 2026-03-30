<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ElasticsearchResult;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResultCursor;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchAfterSizeIdentifierResultCursorFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class SearchAfterSizeIdentifierResultCursorFactoryTest extends TestCase
{
    private SearchAfterSizeIdentifierResultCursorFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new SearchAfterSizeIdentifierResultCursorFactory();
    }

}
