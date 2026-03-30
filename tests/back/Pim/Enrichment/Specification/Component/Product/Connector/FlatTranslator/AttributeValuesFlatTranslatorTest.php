<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValueRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValue\FlatAttributeValueTranslatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValuesFlatTranslator;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PHPUnit\Framework\TestCase;

class AttributeValuesFlatTranslatorTest extends TestCase
{
    private AttributeValuesFlatTranslator $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeValuesFlatTranslator();
    }

}
