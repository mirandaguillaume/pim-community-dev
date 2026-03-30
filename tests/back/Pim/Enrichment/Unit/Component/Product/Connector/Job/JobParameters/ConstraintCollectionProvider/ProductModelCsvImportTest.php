<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductModelCsvImport;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Collection;

class ProductModelCsvImportTest extends TestCase
{
    private ProductModelCsvImport $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductModelCsvImport();
    }

}
