<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Completeness\Model;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use PHPUnit\Framework\TestCase;

class ProductCompletenessTest extends TestCase
{
    private ProductCompleteness $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductCompleteness();
    }

}
