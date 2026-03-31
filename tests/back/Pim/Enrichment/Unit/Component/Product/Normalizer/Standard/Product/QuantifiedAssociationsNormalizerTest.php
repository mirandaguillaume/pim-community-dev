<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\QuantifiedAssociationsNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation\QuantifiedAssociationsMerger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class QuantifiedAssociationsNormalizerTest extends TestCase
{
    private QuantifiedAssociationsMerger|MockObject $quantifiedAssociationsMerger;
    private QuantifiedAssociationsNormalizer $sut;

    protected function setUp(): void
    {
        $this->quantifiedAssociationsMerger = $this->createMock(QuantifiedAssociationsMerger::class);
        $this->sut = new QuantifiedAssociationsNormalizer($this->quantifiedAssociationsMerger);
    }

    public function test_it_normalizes_a_product_without_its_parents_associations(): void
    {
        $variant_level_2 = $this->createMock(ProductInterface::class);

        $this->assertSame([
                        'PACK' => [
                            'products' => [
                                ['identifier' => 'C', 'quantity' => 3],
                            ],
                        ],
                    ], $this->sut->normalizeWithoutParentsAssociations($variant_level_2, 'standard', []));
    }

    public function test_it_normalizes_a_product_with_only_its_parents_associations(): void
    {
        $variant_level_2 = $this->createMock(ProductInterface::class);

        $this->assertSame([
                        'PACK' => [
                            'products' => [
                                ['identifier' => 'A', 'quantity' => 1],
                                ['identifier' => 'B', 'quantity' => 2],
                            ],
                        ],
                    ], $this->sut->normalizeOnlyParentsAssociations($variant_level_2, 'standard', []));
    }

    public function test_it_normalizes_a_product_with_its_parents_associations(): void
    {
        $variant_level_2 = $this->createMock(ProductInterface::class);

        $this->assertSame([
                        'PACK' => [
                            'products' => [
                                ['identifier' => 'A', 'quantity' => 1],
                                ['identifier' => 'B', 'quantity' => 2],
                                ['identifier' => 'C', 'quantity' => 3],
                            ],
                        ],
                    ], $this->sut->normalizeWithParentsAssociations($variant_level_2, 'standard', []));
    }

    public function test_it_normalizes_a_product_nonvariant(): void
    {
        $nonVariantProduct = $this->createMock(EntityWithQuantifiedAssociationsInterface::class);

        $nonVariantProduct->method('normalizeQuantifiedAssociations')->willReturn([
                        'PACK' => [
                            'products' => [
                                ['identifier' => 'A', 'quantity' => 1],
                            ],
                        ],
                    ]);
        $this->assertSame([
                        'PACK' => [
                            'products' => [
                                ['identifier' => 'A', 'quantity' => 1],
                            ],
                        ],
                    ], $this->sut->normalize($nonVariantProduct, 'standard', []));
    }

    public function test_it_normalizes_a_product_variant_and_merge_the_parents_associations_by_default(): void
    {
        $variant_level_2 = $this->createMock(ProductInterface::class);

        $this->assertSame([
                        'PACK' => [
                            'products' => [
                                ['identifier' => 'A', 'quantity' => 1],
                                ['identifier' => 'B', 'quantity' => 2],
                                ['identifier' => 'C', 'quantity' => 3],
                            ],
                        ],
                    ], $this->sut->normalize($variant_level_2, 'standard', []));
    }
}
