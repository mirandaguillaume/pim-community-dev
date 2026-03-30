<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueInterface;
use PHPUnit\Framework\TestCase;

class OptionValueTest extends TestCase
{
    private OptionValue $sut;

    protected function setUp(): void
    {
        $this->sut = new OptionValue();
    }

}
