<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValueWithLinkedData;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValueWithLinkedData;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValueInterface;
use PHPUnit\Framework\TestCase;

class OptionsValueWithLinkedDataTest extends TestCase
{
    private OptionsValueWithLinkedData $sut;

    protected function setUp(): void
    {
        $this->sut = new OptionsValueWithLinkedData();
    }

}
