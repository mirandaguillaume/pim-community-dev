<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ReadModel;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use PHPUnit\Framework\TestCase;

class ConnectorProductModelListTest extends TestCase
{
    private ConnectorProductModelList $sut;

    protected function setUp(): void
    {
        $this->sut = new ConnectorProductModelList();
    }

}
