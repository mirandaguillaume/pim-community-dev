<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ReadModel;

use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use PHPUnit\Framework\TestCase;

class ConnectorProductModelTest extends TestCase
{
    private ConnectorProductModel $sut;

    protected function setUp(): void
    {
        $this->sut = new ConnectorProductModel();
    }

}
