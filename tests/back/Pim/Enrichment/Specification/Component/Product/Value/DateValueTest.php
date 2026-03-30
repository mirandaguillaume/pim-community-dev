<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\DateValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\DateValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use PHPUnit\Framework\TestCase;

class DateValueTest extends TestCase
{
    private DateValue $sut;

    protected function setUp(): void
    {
        $this->sut = new DateValue();
    }

}
