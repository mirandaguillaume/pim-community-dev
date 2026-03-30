<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValueInterface;
use PHPUnit\Framework\TestCase;

class PriceCollectionValueTest extends TestCase
{
    private PriceCollectionValue $sut;

    protected function setUp(): void
    {
        $this->sut = new PriceCollectionValue();
    }

}
