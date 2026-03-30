<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use PHPUnit\Framework\TestCase;

class SearchQueryBuilderTest extends TestCase
{
    private SearchQueryBuilder $sut;

    protected function setUp(): void
    {
        $this->sut = new SearchQueryBuilder();
    }


    // TODO: Custom matchers from getMatchers() need manual conversion
}
