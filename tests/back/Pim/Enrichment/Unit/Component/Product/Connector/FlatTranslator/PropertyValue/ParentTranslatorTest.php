<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue\ParentTranslator;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface;
use PHPUnit\Framework\TestCase;

class ParentTranslatorTest extends TestCase
{
    private ParentTranslator $sut;

    protected function setUp(): void
    {
        $this->sut = new ParentTranslator();
    }

}
