<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Elasticsearch\Sorter\Field;

use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Sorter\Field\FamilySorter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\FieldSorterInterface;
use PHPUnit\Framework\TestCase;

class FamilySorterTest extends TestCase
{
    private FamilySorter $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilySorter();
    }

}
