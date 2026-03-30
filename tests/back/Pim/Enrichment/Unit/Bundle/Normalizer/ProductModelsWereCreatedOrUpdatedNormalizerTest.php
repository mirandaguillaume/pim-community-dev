<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Normalizer;

use Akeneo\Pim\Enrichment\Bundle\Normalizer\ProductModelsWereCreatedOrUpdatedNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductModelWasCreated;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductModelWasUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductModelsWereCreatedOrUpdated;
use PHPUnit\Framework\TestCase;

class ProductModelsWereCreatedOrUpdatedNormalizerTest extends TestCase
{
    private ProductModelsWereCreatedOrUpdatedNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductModelsWereCreatedOrUpdatedNormalizer();
    }

}
