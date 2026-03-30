<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductViolationNormalizer;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;

class ProductViolationNormalizerTest extends TestCase
{
    private ProductViolationNormalizer $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductViolationNormalizer();
    }

}
