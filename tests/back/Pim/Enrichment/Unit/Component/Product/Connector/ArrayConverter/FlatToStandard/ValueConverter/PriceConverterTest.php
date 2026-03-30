<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\PriceConverter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\ValueConverterInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;

class PriceConverterTest extends TestCase
{
    private PriceConverter $sut;

    protected function setUp(): void
    {
        $this->sut = new PriceConverter();
    }

}
