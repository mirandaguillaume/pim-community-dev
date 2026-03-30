<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue\FamilyTranslator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetFamilyTranslations;
use PHPUnit\Framework\TestCase;

class FamilyTranslatorTest extends TestCase
{
    private FamilyTranslator $sut;

    protected function setUp(): void
    {
        $this->sut = new FamilyTranslator();
    }

}
