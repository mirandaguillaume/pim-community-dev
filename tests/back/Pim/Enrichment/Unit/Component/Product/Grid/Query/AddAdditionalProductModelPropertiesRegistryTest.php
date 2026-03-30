<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Grid\Query;

use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductModelProperties;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductModelPropertiesRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use PHPUnit\Framework\TestCase;

class AddAdditionalProductModelPropertiesRegistryTest extends TestCase
{
    private AddAdditionalProductModelPropertiesRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new AddAdditionalProductModelPropertiesRegistry();
    }

}
