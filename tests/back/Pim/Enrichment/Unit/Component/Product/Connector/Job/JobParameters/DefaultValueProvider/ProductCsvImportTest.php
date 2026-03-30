<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\ProductCsvImport;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PHPUnit\Framework\TestCase;

class ProductCsvImportTest extends TestCase
{
    private ProductCsvImport $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductCsvImport();
    }

}
