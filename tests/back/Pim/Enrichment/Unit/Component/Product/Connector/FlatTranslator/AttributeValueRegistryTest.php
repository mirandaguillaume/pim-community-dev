<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValueRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue\FlatAttributeValueTranslatorInterface;
use PHPUnit\Framework\TestCase;

class AttributeValueRegistryTest extends TestCase
{
    private AttributeValueRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeValueRegistry();
    }

}
