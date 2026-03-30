<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ProductAnalyzer;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use PHPUnit\Framework\TestCase;

class ProductAnalyzerTest extends TestCase
{
    private ProductAnalyzer $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductAnalyzer();
    }

}
