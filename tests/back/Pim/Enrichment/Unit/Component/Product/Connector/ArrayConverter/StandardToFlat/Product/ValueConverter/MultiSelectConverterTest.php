<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\MultiSelectConverter;
use PHPUnit\Framework\TestCase;

class MultiSelectConverterTest extends TestCase
{
    private MultiSelectConverter $sut;

    protected function setUp(): void
    {
        $this->sut = new MultiSelectConverter();
    }

}
