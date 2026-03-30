<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AbstractFieldFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\SelfAndAncestorFilterLabelOrIdentifier;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use PHPUnit\Framework\TestCase;

class SelfAndAncestorFilterLabelOrIdentifierTest extends TestCase
{
    private SelfAndAncestorFilterLabelOrIdentifier $sut;

    protected function setUp(): void
    {
        $this->sut = new SelfAndAncestorFilterLabelOrIdentifier(['self_and_ancestor.label_or_identifier'], ['CONTAINS']);
    }

}
