<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use PHPUnit\Framework\TestCase;

class ScalarValueTest extends TestCase
{
    private ScalarValue $sut;

    protected function setUp(): void
    {
        $this->sut = new ScalarValue();
    }

}
