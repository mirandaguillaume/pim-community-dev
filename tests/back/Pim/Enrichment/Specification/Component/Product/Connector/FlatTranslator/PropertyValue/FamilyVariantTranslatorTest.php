<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue\FamilyVariantTranslator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\FamilyVariant\GetFamilyVariantTranslations;
use PHPUnit\Framework\TestCase;

class FamilyVariantTranslatorTest extends TestCase
{
    private FamilyVariantTranslator $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyVariantTranslator();
    }

}
