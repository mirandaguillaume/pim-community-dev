<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ConvertedField;
use PHPUnit\Framework\TestCase;

class ConvertedFieldTest extends TestCase
{
    private ConvertedField $sut;

    protected function setUp(): void
    {
        $this->sut = new ConvertedField();
    }

}
