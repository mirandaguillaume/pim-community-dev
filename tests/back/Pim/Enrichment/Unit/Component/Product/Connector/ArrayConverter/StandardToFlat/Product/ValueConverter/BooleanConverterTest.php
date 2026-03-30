<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\BooleanConverter;
use PHPUnit\Framework\TestCase;

class BooleanConverterTest extends TestCase
{
    private BooleanConverter $sut;

    protected function setUp(): void
    {
        $this->sut = new BooleanConverter();
    }

}
