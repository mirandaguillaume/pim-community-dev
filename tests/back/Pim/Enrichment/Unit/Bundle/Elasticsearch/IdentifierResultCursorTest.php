<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ElasticsearchResult;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResultCursor;
use Akeneo\Pim\Enrichment\Component\Product\Query\ResultAwareInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ResultInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PHPUnit\Framework\TestCase;

class IdentifierResultCursorTest extends TestCase
{
    private IdentifierResultCursor $sut;

    protected function setUp(): void
    {
        $this->sut = new IdentifierResultCursor();
    }

}
