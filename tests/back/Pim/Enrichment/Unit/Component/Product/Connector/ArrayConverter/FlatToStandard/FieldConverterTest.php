<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ConvertedField;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldConverter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;
use Akeneo\Pim\Structure\Component\Repository\GroupTypeRepositoryInterface;
use PHPUnit\Framework\TestCase;

class FieldConverterTest extends TestCase
{
    private FieldConverter $sut;

    protected function setUp(): void
    {
        $this->sut = new FieldConverter();
    }

}
