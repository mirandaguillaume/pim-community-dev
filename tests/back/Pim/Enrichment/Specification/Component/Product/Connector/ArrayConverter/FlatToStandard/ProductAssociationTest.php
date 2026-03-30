<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\Product;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ProductAssociation;
use PHPUnit\Framework\TestCase;

class ProductAssociationTest extends TestCase
{
    private ProductAssociation $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductAssociation();
    }

}
