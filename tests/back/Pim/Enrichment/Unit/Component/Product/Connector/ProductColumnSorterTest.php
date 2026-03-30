<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ProductColumnSorter;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Connector\Writer\File\ColumnSorterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\DefaultColumnSorter;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ProductColumnSorterTest extends TestCase
{
    private ProductColumnSorter $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductColumnSorter();
    }

}
