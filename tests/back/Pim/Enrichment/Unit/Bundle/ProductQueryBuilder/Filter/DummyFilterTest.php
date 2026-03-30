<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\ProductQueryBuilder\Filter;

use Akeneo\Pim\Enrichment\Bundle\ProductQueryBuilder\Filter\DummyFilter;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;

class DummyFilterTest extends TestCase
{
    private DummyFilter $sut;

    protected function setUp(): void
    {
        $this->sut = new DummyFilter();
    }

}
