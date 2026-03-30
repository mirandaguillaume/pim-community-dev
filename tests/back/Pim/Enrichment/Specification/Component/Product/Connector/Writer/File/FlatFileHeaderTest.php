<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Writer\File;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\FlatFileHeader;
use PHPUnit\Framework\TestCase;

class FlatFileHeaderTest extends TestCase
{
    private FlatFileHeader $sut;

    protected function setUp(): void
    {
        $this->sut = new FlatFileHeader();
    }

}
