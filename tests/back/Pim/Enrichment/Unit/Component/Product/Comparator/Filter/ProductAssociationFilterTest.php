<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Comparator\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\ProductAssociationFilter;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductAssociationFilterTest extends TestCase
{
    private ProductAssociationFilter $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductAssociationFilter();
    }

}
