<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataValueInterface;
use PHPUnit\Framework\TestCase;

class ReferenceDataValueTest extends TestCase
{
    private ReferenceDataValue $sut;

    protected function setUp(): void
    {
        $this->sut = new ReferenceDataValue();
    }

}
