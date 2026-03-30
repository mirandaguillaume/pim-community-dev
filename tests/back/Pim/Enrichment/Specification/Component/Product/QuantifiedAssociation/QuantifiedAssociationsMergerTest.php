<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation\QuantifiedAssociationsMerger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuantifiedAssociationsMergerTest extends TestCase
{
    private QuantifiedAssociationsMerger $sut;

    protected function setUp(): void
    {
        $this->sut = new QuantifiedAssociationsMerger();
    }

    public function test_it_merge_quantified_associations(): void
    {
        $product1 = $this->createMock(ProductInterface::class);
        $product2 = $this->createMock(ProductInterface::class);
        $quantifiedAssociations1 = $this->createMock(QuantifiedAssociationCollection::class);
        $quantifiedAssociations2 = $this->createMock(QuantifiedAssociationCollection::class);
        $quantifiedAssociationsMerged = $this->createMock(QuantifiedAssociationCollection::class);

        $product1->method('getQuantifiedAssociations')->willReturn($quantifiedAssociations1);
        $product2->method('getQuantifiedAssociations')->willReturn($quantifiedAssociations2);
        $quantifiedAssociations1->expects($this->once())->method('merge')->with($quantifiedAssociations2)->willReturn($quantifiedAssociationsMerged);
        $quantifiedAssociationsMerged->method('normalize')->willReturn([
                        'PACK' => [
                            'products' => [
                                ['identifier' => 'A', 'quantity' => 2],
                                ['identifier' => 'B', 'quantity' => 3],
                                ['identifier' => 'C', 'quantity' => 42],
                                ['identifier' => 'D', 'quantity' => 5],
                            ],
                            'product_models' => [],
                        ],
                    ]);
        $this->assertSame([
                    'PACK' => [
                        'products' => [
                            ['identifier' => 'A', 'quantity' => 2],
                            ['identifier' => 'B', 'quantity' => 3],
                            ['identifier' => 'C', 'quantity' => 42],
                            ['identifier' => 'D', 'quantity' => 5],
                        ],
                        'product_models' => [],
                    ],
                ], $this->sut->normalizeAndMergeQuantifiedAssociationsFrom([
                    $product1,
                    $product2,
                ]));
    }
}
