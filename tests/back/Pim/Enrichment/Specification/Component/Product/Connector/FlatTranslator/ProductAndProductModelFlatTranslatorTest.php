<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AssociationTranslator;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValuesFlatTranslator;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\HeaderRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header\FlatHeaderTranslatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\ProductAndProductModelFlatTranslator;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValueRegistry;
use PHPUnit\Framework\TestCase;

class ProductAndProductModelFlatTranslatorTest extends TestCase
{
    private ProductAndProductModelFlatTranslator $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductAndProductModelFlatTranslator();
    }

}
