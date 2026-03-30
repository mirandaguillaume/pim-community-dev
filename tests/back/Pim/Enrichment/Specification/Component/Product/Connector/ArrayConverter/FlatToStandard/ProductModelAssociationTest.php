<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ProductModelAssociation;
use PHPUnit\Framework\TestCase;

class ProductModelAssociationTest extends TestCase
{
    private ProductModelAssociation $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductModelAssociation();
    }

}
