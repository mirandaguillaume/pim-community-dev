<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Elasticsearch\Indexer;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\GetElasticsearchProductProjectionInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductProjection;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ProductIndexerTest extends TestCase
{
    private ProductIndexer $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductIndexer();
    }

    private function getElasticSearchProjection(string $identifier, $uuid = null): ElasticsearchProductProjection
    {
            return new ElasticsearchProductProjection(
                $uuid ?? Uuid::fromString('3bf35583-c54e-4f8a-8bd9-5693c142a1cf'),
                $identifier,
                new \DateTimeImmutable('2019-03-16 12:03:00', new \DateTimeZone('UTC')),
                new \DateTimeImmutable('2019-03-16 12:03:00', new \DateTimeZone('UTC')),
                new \DateTimeImmutable('2019-03-16 12:03:00', new \DateTimeZone('UTC')),
                true,
                'family_code',
                [],
                'family_variant_code',
                [],
                [],
                [],
                [],
                null,
                [],
                [],
                [],
                [],
                [],
                [],
                []
            );
        }

    private function getRangeUuids(int $start, int $end): array
    {
            return array_map(fn (): UuidInterface => Uuid::uuid4(), range($start, $end));
        }
}
