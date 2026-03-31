<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation\QuantifiedAssociationsFromAncestorsFilter;
use Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation\QuantifiedAssociationsMerger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociationsFromAncestorsFilterTest extends TestCase
{
    private QuantifiedAssociationsMerger|MockObject $quantifiedAssociationsMerger;
    private QuantifiedAssociationsFromAncestorsFilter $sut;

    protected function setUp(): void
    {
        $this->quantifiedAssociationsMerger = $this->createMock(QuantifiedAssociationsMerger::class);
        $this->sut = new QuantifiedAssociationsFromAncestorsFilter($this->quantifiedAssociationsMerger);
    }

    public function test_it_remove_quantified_associations_on_products_belonging_to_an_ancestor(): void
    {
        $product_model = $this->createMock(ProductModelInterface::class);
        $variant_level_1 = $this->createMock(ProductModelInterface::class);
        $variant_level_2 = $this->createMock(ProductInterface::class);

        $product_model->method('getParent')->willReturn(null);
        $variant_level_1->method('getParent')->willReturn($product_model);
        $variant_level_2->method('getParent')->willReturn($variant_level_1);

        $this->quantifiedAssociationsMerger->method('normalizeAndMergeQuantifiedAssociationsFrom')->with([
                        $product_model,
                        $variant_level_1,
                    ])->willReturn([
                        'PACK' => [
                            'products' => [
                                ['identifier' => 'product_A', 'quantity' => 2],
                                ['identifier' => 'product_B', 'quantity' => 3],
                            ],
                        ],
                    ]);
        $mergedQuantifiedAssociations = [
                    'PACK' => [
                        'products' => [
                            ['identifier' => 'product_A', 'quantity' => 2],
                            ['identifier' => 'product_B', 'quantity' => 3],
                            ['identifier' => 'product_C', 'quantity' => 4],
                        ],
                    ],
                ];
        $expectedQuantifiedAssociations = [
                    'PACK' => [
                        'products' => [
                            ['identifier' => 'product_C', 'quantity' => 4],
                        ],
                        'product_models' => [],
                    ],
                ];
        $this->assertSame($expectedQuantifiedAssociations, $this->sut->filter($mergedQuantifiedAssociations, $variant_level_2));
    }

    public function test_it_remove_quantified_associations_on_product_models_belonging_to_an_ancestor(): void
    {
        $product_model = $this->createMock(ProductModelInterface::class);
        $variant_level_1 = $this->createMock(ProductModelInterface::class);
        $variant_level_2 = $this->createMock(ProductInterface::class);

        $product_model->method('getParent')->willReturn(null);
        $variant_level_1->method('getParent')->willReturn($product_model);
        $variant_level_2->method('getParent')->willReturn($variant_level_1);

        $this->quantifiedAssociationsMerger->method('normalizeAndMergeQuantifiedAssociationsFrom')->with([
                        $product_model,
                        $variant_level_1,
                    ])->willReturn([
                        'PACK' => [
                            'product_models' => [
                                ['identifier' => 'productmodel_A', 'quantity' => 2],
                                ['identifier' => 'productmodel_B', 'quantity' => 3],
                            ],
                        ],
                    ]);
        $mergedQuantifiedAssociations = [
                    'PACK' => [
                        'product_models' => [
                            ['identifier' => 'productmodel_A', 'quantity' => 2],
                            ['identifier' => 'productmodel_B', 'quantity' => 3],
                            ['identifier' => 'productmodel_C', 'quantity' => 4],
                        ],
                    ],
                ];
        $expectedQuantifiedAssociations = [
                    'PACK' => [
                        'products' => [],
                        'product_models' => [
                            ['identifier' => 'productmodel_C', 'quantity' => 4],
                        ],
                    ],
                ];
        $this->assertSame($expectedQuantifiedAssociations, $this->sut->filter($mergedQuantifiedAssociations, $variant_level_2));
    }

    public function test_it_preserve_quantified_associations_on_products_when_quantity_has_been_overwritten(): void
    {
        $product_model = $this->createMock(ProductModelInterface::class);
        $variant_level_1 = $this->createMock(ProductModelInterface::class);

        $product_model->method('getParent')->willReturn(null);
        $variant_level_1->method('getParent')->willReturn($product_model);

        $this->quantifiedAssociationsMerger->method('normalizeAndMergeQuantifiedAssociationsFrom')->with([
                        $product_model,
                    ])->willReturn([
                        'PACK' => [
                            'products' => [
                                ['identifier' => 'product_A', 'quantity' => 2],
                            ],
                        ],
                    ]);
        $mergedQuantifiedAssociations = [
                    'PACK' => [
                        'products' => [
                            ['identifier' => 'product_A', 'quantity' => 42],
                        ],
                    ],
                ];
        $expectedQuantifiedAssociations = [
                    'PACK' => [
                        'products' => [
                            ['identifier' => 'product_A', 'quantity' => 42],
                        ],
                        'product_models' => [],
                    ],
                ];
        $this->assertSame($expectedQuantifiedAssociations, $this->sut->filter($mergedQuantifiedAssociations, $variant_level_1));
    }

    public function test_it_preserve_quantified_associations_on_product_models_when_quantity_has_been_overwritten(): void
    {
        $product_model = $this->createMock(ProductModelInterface::class);
        $variant_level_1 = $this->createMock(ProductModelInterface::class);

        $product_model->method('getParent')->willReturn(null);
        $variant_level_1->method('getParent')->willReturn($product_model);

        $this->quantifiedAssociationsMerger->method('normalizeAndMergeQuantifiedAssociationsFrom')->with([
                        $product_model,
                    ])->willReturn([
                        'PACK' => [
                            'product_models' => [
                                ['identifier' => 'productmodel_A', 'quantity' => 2],
                            ],
                        ],
                    ]);
        $mergedQuantifiedAssociations = [
                    'PACK' => [
                        'product_models' => [
                            ['identifier' => 'productmodel_A', 'quantity' => 42],
                        ],
                    ],
                ];
        $expectedQuantifiedAssociations = [
                    'PACK' => [
                        'products' => [],
                        'product_models' => [
                            ['identifier' => 'productmodel_A', 'quantity' => 42],
                        ],
                    ],
                ];
        $this->assertSame($expectedQuantifiedAssociations, $this->sut->filter($mergedQuantifiedAssociations, $variant_level_1));
    }
}
