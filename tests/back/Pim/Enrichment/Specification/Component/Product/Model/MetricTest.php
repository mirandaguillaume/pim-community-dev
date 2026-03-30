<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\Metric;
use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use PHPUnit\Framework\TestCase;

class MetricTest extends TestCase
{
    private Metric $sut;

    protected function setUp(): void
    {
        $this->sut = new Metric();
    }

}
