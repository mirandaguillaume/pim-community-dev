<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\ValueConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\ValueConverterRegistry;
use PHPUnit\Framework\TestCase;

class ValueConverterRegistryTest extends TestCase
{
    private ValueConverterRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new ValueConverterRegistry();
    }

}
