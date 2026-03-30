<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Writer\Database;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\ProductWriter;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PHPUnit\Framework\TestCase;

class ProductWriterTest extends TestCase
{
    private ProductWriter $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductWriter();
    }

}
