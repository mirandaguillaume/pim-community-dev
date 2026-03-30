<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\HeaderRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header\FlatHeaderTranslatorInterface;
use PHPUnit\Framework\TestCase;

class HeaderRegistryTest extends TestCase
{
    private HeaderRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new HeaderRegistry();
    }

}
