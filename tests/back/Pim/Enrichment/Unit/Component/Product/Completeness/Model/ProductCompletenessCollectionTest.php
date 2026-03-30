<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Completeness\Model;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ProductCompletenessCollectionTest extends TestCase
{
    private ProductCompletenessCollection $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductCompletenessCollection();
    }

}
