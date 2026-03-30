<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Reader\File\Xlsx;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\Xlsx\ProductAssociationReader;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorFactory;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;
use Akeneo\Tool\Component\Connector\Reader\File\Xlsx\Reader;
use PHPUnit\Framework\TestCase;

class ProductAssociationReaderTest extends TestCase
{
    private ProductAssociationReader $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductAssociationReader();
    }

}
