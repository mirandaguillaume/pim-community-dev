<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ColumnsMapper;
use PHPUnit\Framework\TestCase;

class ColumnsMapperTest extends TestCase
{
    private ColumnsMapper $sut;

    protected function setUp(): void
    {
        $this->sut = new ColumnsMapper();
    }

}
