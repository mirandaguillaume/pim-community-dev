<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValueRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue\FlatPropertyValueTranslatorInterface;
use PHPUnit\Framework\TestCase;

class PropertyValueRegistryTest extends TestCase
{
    private PropertyValueRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new PropertyValueRegistry();
    }

}
