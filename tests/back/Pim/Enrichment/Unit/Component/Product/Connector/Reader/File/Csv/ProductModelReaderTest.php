<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Reader\File\Csv;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\Csv\ProductModelReader;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorFactory;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorInterface;
use Akeneo\Tool\Component\Connector\Reader\File\FileReaderInterface;
use Akeneo\Tool\Component\Connector\Reader\File\MediaPathTransformer;
use PHPUnit\Framework\TestCase;

class ProductModelReaderTest extends TestCase
{
    private ProductModelReader $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductModelReader();
    }

}
