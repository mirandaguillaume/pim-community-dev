<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Filter;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\ProductEditDataFilter;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PHPUnit\Framework\TestCase;

class ProductEditDataFilterTest extends TestCase
{
    private ProductEditDataFilter $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductEditDataFilter();
    }

}
