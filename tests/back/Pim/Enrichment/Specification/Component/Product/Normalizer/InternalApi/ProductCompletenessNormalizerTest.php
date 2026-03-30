<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductCompletenessNormalizer;
use PHPUnit\Framework\TestCase;

class ProductCompletenessNormalizerTest extends TestCase
{
    private ProductCompletenessNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductCompletenessNormalizer();
    }

}
