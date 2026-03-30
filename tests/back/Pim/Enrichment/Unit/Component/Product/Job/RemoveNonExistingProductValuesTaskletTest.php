<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResults;
use Akeneo\Pim\Enrichment\Component\Product\Job\RemoveNonExistingProductValuesTasklet;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\GetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PHPUnit\Framework\TestCase;

class RemoveNonExistingProductValuesTaskletTest extends TestCase
{
    private RemoveNonExistingProductValuesTasklet $sut;

    protected function setUp(): void
    {
        $this->sut = new RemoveNonExistingProductValuesTasklet();
    }

    protected function createAttribute(): Attribute
    {
            return new Attribute(
                'color',
                'pim_catalog_simpleselect',
                [],
                false,
                false,
                null,
                null,
                null,
                'option',
                []
            );
        }
}
