<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\MetricFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\Metric;
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\UnitNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager;
use PHPUnit\Framework\TestCase;

class MetricFactoryTest extends TestCase
{
    private MetricFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new MetricFactory();
    }

}
