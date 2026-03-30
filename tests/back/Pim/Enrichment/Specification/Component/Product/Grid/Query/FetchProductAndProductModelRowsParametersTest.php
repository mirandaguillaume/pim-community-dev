<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Grid\Query;

use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use PHPUnit\Framework\TestCase;

class FetchProductAndProductModelRowsParametersTest extends TestCase
{
    private FetchProductAndProductModelRowsParameters $sut;

    protected function setUp(): void
    {
        $this->sut = new FetchProductAndProductModelRowsParameters();
    }

}
