<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductCompletenessWithMissingAttributeCodesCollectionNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetAttributeLabelsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetChannelLabelsInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductCompletenessWithMissingAttributeCodesCollectionNormalizerTest extends TestCase
{
    private ProductCompletenessWithMissingAttributeCodesCollectionNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductCompletenessWithMissingAttributeCodesCollectionNormalizer();
    }

}
