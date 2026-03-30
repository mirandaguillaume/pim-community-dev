<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ProductModelNormalizer;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelNormalizerTest extends TestCase
{
    private ProductModelNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductModelNormalizer();
    }


    // TODO: Custom matchers from getMatchers() need manual conversion
}
