<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\MediaConverter;
use PHPUnit\Framework\TestCase;

class MediaConverterTest extends TestCase
{
    private MediaConverter $sut;

    protected function setUp(): void
    {
        $this->sut = new MediaConverter();
    }

}
