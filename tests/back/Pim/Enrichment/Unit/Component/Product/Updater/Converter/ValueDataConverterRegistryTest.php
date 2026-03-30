<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Updater\Converter;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\ValueDataConverter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Converter\ValueDataConverterRegistry;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;

class ValueDataConverterRegistryTest extends TestCase
{
    private ValueDataConverterRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new ValueDataConverterRegistry();
    }

}
