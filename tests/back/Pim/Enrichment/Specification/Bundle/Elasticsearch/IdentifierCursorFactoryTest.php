<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierCursor;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierCursorFactory;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use PHPUnit\Framework\TestCase;

class IdentifierCursorFactoryTest extends TestCase
{
    private IdentifierCursorFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new IdentifierCursorFactory();
    }

}
