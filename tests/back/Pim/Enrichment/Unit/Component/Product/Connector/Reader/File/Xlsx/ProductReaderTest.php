<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Reader\File\Xlsx;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\Xlsx\ProductReader;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorFactory;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorInterface;
use Akeneo\Tool\Component\Connector\Reader\File\MediaPathTransformer;
use Akeneo\Tool\Component\Connector\Reader\File\Xlsx\Reader;
use PHPUnit\Framework\TestCase;

class ProductReaderTest extends TestCase
{
    private ProductReader $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductReader();
    }

}
