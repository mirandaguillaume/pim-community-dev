<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValueInterface;
use PHPUnit\Framework\TestCase;

class ReferenceDataCollectionValueTest extends TestCase
{
    private ReferenceDataCollectionValue $sut;

    protected function setUp(): void
    {
        $this->sut = new ReferenceDataCollectionValue();
    }

}
