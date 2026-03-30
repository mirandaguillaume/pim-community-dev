<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductModelProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelIndexer;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductModelProjection;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use PHPUnit\Framework\TestCase;

class ProductModelIndexerTest extends TestCase
{
    private ProductModelIndexer $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductModelIndexer();
    }

    private function getFakeProjection(string $code = 'code'): ElasticsearchProductModelProjection
    {
            return new ElasticsearchProductModelProjection(
                1,
                $code,
                new \DateTimeImmutable('2000-12-30'),
                new \DateTimeImmutable('2000-12-31'),
                new \DateTimeImmutable('2000-12-31'),
                'familyCode',
                [],
                'familyVariantCode',
                [],
                [],
                'parentCode',
                [],
                [],
                [],
                null,
                [],
                [],
                []
            );
        }

    private function getRangeCodes(int $start, int $end): array
    {
            return preg_filter('/^/', 'pm_', range($start, $end));
        }
}
