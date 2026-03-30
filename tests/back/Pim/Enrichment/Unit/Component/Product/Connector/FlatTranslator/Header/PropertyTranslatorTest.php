<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator\Header;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header\PropertyTranslator;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;
use PHPUnit\Framework\TestCase;

class PropertyTranslatorTest extends TestCase
{
    private PropertyTranslator $sut;

    protected function setUp(): void
    {
        $this->sut = new PropertyTranslator();
    }

}
