<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Processor\Denormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductModelLoaderProcessor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PHPUnit\Framework\TestCase;

class ProductModelLoaderProcessorTest extends TestCase
{
    private ProductModelLoaderProcessor $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductModelLoaderProcessor();
    }

}
