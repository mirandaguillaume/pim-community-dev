<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Writer\Database\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\Database\MassEdit\ProductAndProductModelWriter;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PHPUnit\Framework\TestCase;

class ProductAndProductModelWriterTest extends TestCase
{
    private ProductAndProductModelWriter $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductAndProductModelWriter();
    }

}
