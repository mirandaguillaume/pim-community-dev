<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Elasticsearch\Model;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductModelProjection;
use PHPUnit\Framework\TestCase;

class ElasticsearchProductModelProjectionTest extends TestCase
{
    private ElasticsearchProductModelProjection $sut;

    protected function setUp(): void
    {
        $this->sut = new ElasticsearchProductModelProjection();
    }

}
