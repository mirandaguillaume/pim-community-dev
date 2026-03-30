<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue\MetricTranslator;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\GetUnitTranslations;
use PHPUnit\Framework\TestCase;

class MetricTranslatorTest extends TestCase
{
    private MetricTranslator $sut;

    protected function setUp(): void
    {
        $this->sut = new MetricTranslator();
    }

}
