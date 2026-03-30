<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue\EnabledTranslator;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;
use PHPUnit\Framework\TestCase;

class EnabledTranslatorTest extends TestCase
{
    private EnabledTranslator $sut;

    protected function setUp(): void
    {
        $this->sut = new EnabledTranslator();
    }

}
