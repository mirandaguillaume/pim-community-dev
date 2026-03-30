<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\QualityScoreConverter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScoresInterface;
use PHPUnit\Framework\TestCase;

class QualityScoreConverterTest extends TestCase
{
    private QualityScoreConverter $sut;

    protected function setUp(): void
    {
        $this->sut = new QualityScoreConverter();
    }

}
