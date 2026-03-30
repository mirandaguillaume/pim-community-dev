<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\MissingRequiredAttributesNormalizer;
use PHPUnit\Framework\TestCase;

class MissingRequiredAttributesNormalizerTest extends TestCase
{
    private MissingRequiredAttributesNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new MissingRequiredAttributesNormalizer();
    }

}
