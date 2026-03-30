<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;
use PHPUnit\Framework\TestCase;

class FieldSplitterTest extends TestCase
{
    private FieldSplitter $sut;

    protected function setUp(): void
    {
        $this->sut = new FieldSplitter();
    }

}
