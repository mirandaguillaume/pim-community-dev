<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\ProductLocalized;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use PHPUnit\Framework\TestCase;

class ProductLocalizedTest extends TestCase
{
    private ProductLocalized $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductLocalized();
    }

}
