<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\ProductModel\Query;

use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\CompleteVariantProducts;
use PHPUnit\Framework\TestCase;

class CompleteVariantProductsTest extends TestCase
{
    private CompleteVariantProducts $sut;

    protected function setUp(): void
    {
        $this->sut = new CompleteVariantProducts();
    }

}
