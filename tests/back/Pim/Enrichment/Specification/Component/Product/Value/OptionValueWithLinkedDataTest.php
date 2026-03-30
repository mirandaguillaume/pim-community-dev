<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueWithLinkedData;
use PHPUnit\Framework\TestCase;

class OptionValueWithLinkedDataTest extends TestCase
{
    private OptionValueWithLinkedData $sut;

    protected function setUp(): void
    {
        $this->sut = new OptionValueWithLinkedData();
    }

}
