<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Elasticsearch\PublicApi\Write;

use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\IndexConfiguration;

class MigrateIndexWithoutDowntime
{
    public function __construct(private readonly string $indexAlias, private readonly IndexConfiguration $indexConfiguration, private readonly \Closure $findUpdatedDocumentQuery)
    {
    }

    public function getIndexAlias(): string
    {
        return $this->indexAlias;
    }

    public function getIndexConfiguration(): IndexConfiguration
    {
        return $this->indexConfiguration;
    }

    public function getFindUpdatedDocumentQuery(): \Closure
    {
        return $this->findUpdatedDocumentQuery;
    }
}
