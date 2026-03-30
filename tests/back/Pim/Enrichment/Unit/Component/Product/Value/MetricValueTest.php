<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use PHPUnit\Framework\TestCase;

class MetricValueTest extends TestCase
{
    private MetricValue $sut;

    protected function setUp(): void
    {
        $this->sut = new MetricValue();
    }

}
