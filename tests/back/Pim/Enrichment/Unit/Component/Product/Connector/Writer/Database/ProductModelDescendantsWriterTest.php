<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Writer\Database;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\ProductModelDescendantsWriter;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PHPUnit\Framework\TestCase;

class ProductModelDescendantsWriterTest extends TestCase
{
    private ProductModelDescendantsWriter $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductModelDescendantsWriter();
    }

}
