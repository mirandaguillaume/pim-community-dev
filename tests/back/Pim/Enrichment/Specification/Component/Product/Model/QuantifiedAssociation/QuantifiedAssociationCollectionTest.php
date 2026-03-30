<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\UuidMapping;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociationCollectionTest extends TestCase
{
    private QuantifiedAssociationCollection $sut;

    protected function setUp(): void
    {
        $this->sut = QuantifiedAssociationCollection::createWithAssociationsAndMapping([
                    'PACK' => [
                        'products'       => [
                            ['id' => 1, 'quantity' => 1],
                            ['id' => 2, 'quantity' => 2],
                        ],
                        'product_models' => [
                            ['id' => 1, 'quantity' => 1],
                            ['id' => 2, 'quantity' => 2],
                        ],
                    ]
                ],
                $this->aUuidMapping(),
                $this->anIdMapping(),
                ['PACK']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(QuantifiedAssociationCollection::class, $this->sut);
    }

    public function test_it_is_normalizable(): void
    {
        $expectedRawQuantifiedAssociations = [
                    'PACK' => [
                        'products'       => [
                            ['id' => 1, 'uuid' => '3f090f5e-3f54-4f34-879c-87779297d130', 'quantity' => 1],
                            ['id' => 2, 'uuid' => '52254bba-a2c8-40bb-abe1-195e3970bd93', 'quantity' => 2],
                        ],
                        'product_models' => [
                            ['id' => 1, 'quantity' => 1],
                            ['id' => 2, 'quantity' => 2],
                        ],
                    ]
                ];
        $this->sut = QuantifiedAssociationCollection::createWithAssociationsAndMapping($expectedRawQuantifiedAssociations,
                        $this->aUuidMapping(),
                        $this->anIdMapping(),
                        ['PACK']);
        $this->assertSame($expectedRawQuantifiedAssociations, $this->sut->normalizeWithMapping(
                    $this->aUuidMapping(),
                    $this->anIdMapping(),
                ));
    }

    public function test_it_ignores_unknown_products_product_models_and_association_types(): void
    {
        $rawQuantifiedAssociations = [
                    'PACK' => [
                        'products'       => [
                            ['id' => 1, 'quantity' => 1],
                            ['id' => 2, 'quantity' => 2],
                        ],
                        'product_models' => [
                            ['id' => 1, 'quantity' => 1],
                            ['id' => 2, 'quantity' => 2],
                        ],
                    ],
                    'NON_EXISTENT_ASSOCIATION_TYPE' => [
                        'products'       => [
                            ['id' => 1, 'quantity' => 1],
                        ],
                        'product_models' => [
                            ['id' => 1, 'quantity' => 1],
                        ],
                    ]
                ];
        $expectedNormalizedAssociations = [
                    'PACK' => [
                        'products'       => [
                            ['id' => 1, 'uuid' => '3f090f5e-3f54-4f34-879c-87779297d130', 'quantity' => 1],
                        ],
                        'product_models' => [
                            ['id' => 1, 'quantity' => 1],
                        ],
                    ]
                ];
        $this->sut = QuantifiedAssociationCollection::createWithAssociationsAndMapping($rawQuantifiedAssociations,
                        $this->anIncompleteUuidMapping(),
                        $this->anIncompleteIdMapping(),
                        ['PACK']);
        $this->assertSame($expectedNormalizedAssociations, $this->sut->normalizeWithMapping(
                    $this->aUuidMapping(),
                    $this->anIncompleteIdMapping()
                ));
    }

    public function test_it_returns_the_list_of_product_identifiers_or_uuids(): void
    {
        $expectedRawQuantifiedAssociations = [
                    'PACK' => [
                        'products'       => [
                            ['id' => 1, 'uuid' => '3f090f5e-3f54-4f34-879c-87779297d130', 'quantity' => 1],
                            ['id' => 2, 'uuid' => '52254bba-a2c8-40bb-abe1-195e3970bd93', 'quantity' => 2],
                            ['id' => 3, 'uuid' => '3aa5cfe1-83d0-4677-ae7f-d9d3c9f085b7', 'quantity' => 1], // association without identifier
                        ],
                        'product_models' => [],
                    ],
                    'PRODUCT_SET' => [
                        'products' => [
                            ['id' => 1, 'uuid' => '3f090f5e-3f54-4f34-879c-87779297d130', 'quantity' => 3],
                        ],
                        'product_models' => [],
                    ]
                ];
        $idMapping = IdMapping::createFromMapping(
                    [
                        1 => 'entity_1',
                        2 => 'entity_2'
                    ]
                );
        $this->sut = QuantifiedAssociationCollection::createWithAssociationsAndMapping($expectedRawQuantifiedAssociations,
                        $this->aUuidMapping(),
                        $idMapping,
                        ['PACK', 'PRODUCT_SET']);
        $this->assertSame(['entity_1', 'entity_2'], $this->sut->getQuantifiedAssociationsProductIdentifiers());
        $this->assertEquals([
                    Uuid::fromString('3f090f5e-3f54-4f34-879c-87779297d130'),
                    Uuid::fromString('52254bba-a2c8-40bb-abe1-195e3970bd93'),
                    Uuid::fromString('3aa5cfe1-83d0-4677-ae7f-d9d3c9f085b7'),
                ], $this->sut->getQuantifiedAssociationsProductUuids());
    }

    public function test_it_returns_the_list_of_product_model_codes(): void
    {
        $expectedRawQuantifiedAssociations = [
                    'PACK' => [
                        'products'       => [],
                        'product_models' => [
                            ['id' => 1, 'quantity' => 1],
                            ['id' => 2, 'quantity' => 2],
                        ],
                    ],
                    'PRODUCT_SET' => [
                        'products' => [],
                        'product_models' => [
                            ['id' => 1, 'quantity' => 3],
                        ],
                    ]
                ];
        $idMapping = IdMapping::createFromMapping(
                    [
                        1 => 'entity_1',
                        2 => 'entity_2'
                    ]
                );
        $this->sut = QuantifiedAssociationCollection::createWithAssociationsAndMapping($expectedRawQuantifiedAssociations,
                        $this->aUuidMapping(),
                        $idMapping,
                        ['PACK', 'PRODUCT_SET']);
        $this->assertSame(['entity_1', 'entity_2'], $this->sut->getQuantifiedAssociationsProductModelCodes());
    }

    public function test_it_cannot_be_created_if_the_raw_associations_does_not_have_a_list_of_product_associations(): void
    {
        $expectedRawQuantifiedAssociations = [
                    'PACK' => [
                        // No 'products' quantified associations
                        'product_models' => [],
                    ]
                ];
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createWithAssociationsAndMapping($expectedRawQuantifiedAssociations,
                            $this->aUuidMapping(),
                            $this->anIdMapping(),
                            ['PACK']);
    }

    public function test_it_cannot_be_created_if_a_product_raw_quantified_link_does_not_have_an_id(): void
    {
        $expectedRawQuantifiedAssociations = [
                    'PACK' => [
                        'products' => [
                            ['quantity' => 1], // no 'id'
                        ],
                        'product_models' => [],
                    ]
                ];
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createWithAssociationsAndMapping($expectedRawQuantifiedAssociations,
                            $this->aUuidMapping(),
                            $this->anIdMapping(),
                            ['PACK']);
    }

    public function test_it_cannot_be_created_if_a_product_raw_quantified_link_does_not_have_a_quantity(): void
    {
        $expectedRawQuantifiedAssociations = [
                    'PACK' => [
                        'products' => [
                            ['id' => 1], // no 'quantity'
                        ],
                        'product_models' => [],
                    ]
                ];
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createWithAssociationsAndMapping($expectedRawQuantifiedAssociations,
                            $this->aUuidMapping(),
                            $this->anIdMapping(),
                            ['PACK']);
    }

    public function test_it_cannot_be_created_if_the_raw_associations_does_not_have_a_list_of_product_models_associations(): void
    {
        $expectedRawQuantifiedAssociations = [
                    'PACK' => [
                        'products' => [],
                        // No 'product_models' quantified associations
                    ]
                ];
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createWithAssociationsAndMapping($expectedRawQuantifiedAssociations,
                            $this->aUuidMapping(),
                            $this->anIdMapping(),
                            ['PACK']);
    }

    public function test_it_cannot_be_created_if_a_product_model_raw_quantified_link_does_not_have_an_id(): void
    {
        $expectedRawQuantifiedAssociations = [
                    'PACK' => [
                        'products' => [],
                        'product_models' => [
                            ['quantity' => 1], // no 'id'
                        ],
                    ]
                ];
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createWithAssociationsAndMapping($expectedRawQuantifiedAssociations,
                            $this->aUuidMapping(),
                            $this->anIdMapping(),
                            ['PACK']);
    }

    public function test_it_cannot_be_created_if_a_product_model_raw_quantified_link_does_not_have_a_quantity(): void
    {
        $expectedRawQuantifiedAssociations = [
                    'PACK' => [
                        'products' => [],
                        'product_models' => [
                            ['id' => 1], // no 'quantity'
                        ],
                    ]
                ];
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->createWithAssociationsAndMapping($expectedRawQuantifiedAssociations,
                            $this->aUuidMapping(),
                            $this->anIdMapping(),
                            ['PACK']);
    }

    public function test_it_filter_by_product_identifiers(): void
    {
        $this->sut = QuantifiedAssociationCollection::createFromNormalized([
                            'PACK' => [
                                'products' => [
                                    ['identifier' => 'A', 'quantity' => 2],
                                    ['identifier' => 'B', 'quantity' => 3],
                                    ['identifier' => 'C', 'quantity' => 4],
                                ],
                                'product_models' => [
                                    ['identifier' => 'A', 'quantity' => 2],
                                    ['identifier' => 'B', 'quantity' => 3],
                                ],
                            ],
                            'PRODUCTSET' => [
                                'products' => [
                                    ['identifier' => 'B', 'quantity' => 3],
                                ],
                                'product_models' => [],
                            ],
                        ],);
        $this->assertSame([
                    'PACK' => [
                        'products' => [
                            ['identifier' => 'A', 'quantity' => 2],
                        ],
                        'product_models' => [
                            ['identifier' => 'A', 'quantity' => 2],
                            ['identifier' => 'B', 'quantity' => 3],
                        ],
                    ],
                    'PRODUCTSET' => [
                        'products' => [],
                        'product_models' => [],
                    ],
                ], $this->sut->filterProductIdentifiers(['A'])->normalize());
    }

    public function test_it_filter_by_product_model_codes(): void
    {
        $this->sut = QuantifiedAssociationCollection::createFromNormalized([
                            'PACK' => [
                                'products' => [
                                    ['identifier' => 'A', 'quantity' => 2],
                                    ['identifier' => 'B', 'quantity' => 3],
                                ],
                                'product_models' => [
                                    ['identifier' => 'A', 'quantity' => 2],
                                    ['identifier' => 'B', 'quantity' => 3],
                                    ['identifier' => 'C', 'quantity' => 4],
                                ],
                            ],
                            'PRODUCTSET' => [
                                'products' => [
                                    ['identifier' => 'B', 'quantity' => 3],
                                ],
                                'product_models' => [
                                    ['identifier' => 'B', 'quantity' => 5],
                                ],
                            ],
                        ],);
        $this->assertSame([
                    'PACK' => [
                        'products' => [
                            ['identifier' => 'A', 'quantity' => 2],
                            ['identifier' => 'B', 'quantity' => 3],
                        ],
                        'product_models' => [
                            ['identifier' => 'A', 'quantity' => 2],
                        ],
                    ],
                    'PRODUCTSET' => [
                        'products' => [
                            ['identifier' => 'B', 'quantity' => 3],
                        ],
                        'product_models' => [],
                    ],
                ], $this->sut->filterProductModelCodes(['A'])->normalize());
    }

    public function test_it_clear_quantified_associations_already_empty(): void
    {
        $this->sut = QuantifiedAssociationCollection::createFromNormalized([]);
        $this->assertSame([], $this->sut->clearQuantifiedAssociations()->normalize());
    }

    public function test_it_clear_all_quantified_associations_already_empty(): void
    {
        $this->sut = QuantifiedAssociationCollection::createFromNormalized([
                        'PRODUCTSET_A' => [
                            'products' => [
                                ['identifier' => 'AKN_TS1', 'quantity' => 2],
                            ],
                            'product_models' => [
                                ['identifier' => 'MODEL_AKN_TS1', 'quantity' => 2],
                            ],
                        ],
                        'PRODUCTSET_B' => [
                            'products' => [
                                ['identifier' => 'AKN_TSH2', 'quantity' => 2],
                            ],
                            'product_models' => [],
                        ],
                    ],);
        $this->assertSame([
                    'PRODUCTSET_A' => [
                        'products' => [],
                        'product_models' => [],
                    ],
                    'PRODUCTSET_B' => [
                        'products' => [],
                        'product_models' => [],
                    ],
                ], $this->sut->clearQuantifiedAssociations()->normalize());
    }

    public function test_it_override_empty_quantified_associations(): void
    {
        $this->sut = QuantifiedAssociationCollection::createFromNormalized([]);
        $this->assertSame([
                    'PRODUCTSET_A' => [
                        'products' => [
                            ['identifier' => 'AKN_TS1', 'quantity' => 2],
                        ],
                        'product_models' => [],
                    ],
                ], $this->sut->patchQuantifiedAssociations([
                    'PRODUCTSET_A' => [
                        'products' => [
                            ['identifier' => 'AKN_TS1', 'quantity' => 2],
                        ],
                    ]
                ])->normalize());
    }

    public function test_it_override_existing_quantified_associations(): void
    {
        $this->sut = QuantifiedAssociationCollection::createFromNormalized([
                        'PRODUCTSET_A' => [
                            'products' => [
                                ['identifier' => 'AKN_TS1', 'quantity' => 2],
                            ],
                            'product_models' => [
                                ['identifier' => 'MODEL_AKN_TS1', 'quantity' => 2],
                            ],
                        ],
                        'PRODUCTSET_B' => [
                            'products' => [
                                ['identifier' => 'AKN_TSH2', 'quantity' => 2],
                            ],
                            'product_models' => [],
                        ],
                    ],);
        $this->assertSame([
                    'PRODUCTSET_A' => [
                        'products' => [
                            ['identifier' => 'AKN_TS1_ALT', 'quantity' => 200],
                        ],
                        'product_models' => [
                            ['identifier' => 'MODEL_AKN_TS1', 'quantity' => 2],
                        ],
                    ],
                    'PRODUCTSET_B' => [
                        'products' => [
                            ['identifier' => 'AKN_TSH2', 'quantity' => 2],
                        ],
                        'product_models' => [],
                    ],
                    'PRODUCTSET_C' => [
                        'products' => [
                            ['identifier' => 'AKN_TS1_ALT', 'quantity' => 200],
                        ],
                        'product_models' => [],
                    ]
                ], $this->sut->patchQuantifiedAssociations([
                    'PRODUCTSET_A' => [
                        'products' => [
                            ['identifier' => 'AKN_TS1_ALT', 'quantity' => 200],
                        ],
                    ],
                    'PRODUCTSET_C' => [
                        'products' => [
                            ['identifier' => 'AKN_TS1_ALT', 'quantity' => 200],
                        ],
                    ]
                ])->normalize());
    }

    public function test_it_merge_quantified_associations_and_overwrite_quantities_from_duplicated_identifiers(): void
    {
        $this->sut = QuantifiedAssociationCollection::createFromNormalized([
                            'PACK' => [
                                'products' => [
                                    ['identifier' => 'A', 'quantity' => 2],
                                    ['identifier' => 'B', 'quantity' => 3],
                                    ['identifier' => 'C', 'quantity' => 4],
                                ],
                            ],
                        ]);
        $quantifiedAssociationsToMerge = QuantifiedAssociationCollection::createFromNormalized([
                    'PACK' => [
                        'products' => [
                            ['identifier' => 'B', 'quantity' => 3],
                            ['identifier' => 'C', 'quantity' => 6],
                            ['identifier' => 'D', 'quantity' => 1],
                        ],
                    ],
                ]);
        $this->assertEquals(QuantifiedAssociationCollection::createFromNormalized([
                    'PACK' => [
                        'products' => [
                            ['identifier' => 'A', 'quantity' => 2],
                            ['identifier' => 'B', 'quantity' => 3],
                            ['identifier' => 'C', 'quantity' => 6],
                            ['identifier' => 'D', 'quantity' => 1],
                        ],
                        'product_models' => []
                    ],
                ]), $this->sut->merge($quantifiedAssociationsToMerge));
    }

    public function test_it_can_compare_itself_to_another_collection(): void
    {
        $this->sut = QuantifiedAssociationCollection::createFromNormalized([
                            'type1' => [
                                'products' => [
                                    ['identifier' => 'foo', 'quantity' => 2],
                                    ['identifier' => 'bar', 'quantity' => 5],
                                ],
                                'product_models' => [
                                    ['identifier' => 'baz', 'quantity' => 3],
                                ],
                            ],
                            'type2' => [
                                'products' => [
                                    ['identifier' => 'foo', 'quantity' => 10],
                                ],
                            ],
                        ],);
        $identicalCollection = QuantifiedAssociationCollection::createFromNormalized(
                    [
                        'type2' => [
                            'product_models' => [],
                            'products' => [
                                ['quantity' => 10, 'identifier' => 'foo'],
                            ],
                        ],
                        'type1' => [
                            'product_models' => [
                                ['identifier' => 'baz', 'quantity' => 3],
                            ],
                            'products' => [
                                ['identifier' => 'bar', 'quantity' => 5],
                                ['identifier' => 'foo', 'quantity' => 2],
                            ],
                        ],
                    ]
                );
        $this->assertSame(true, $this->sut->equals($identicalCollection));
        $differentCollection = $identicalCollection = QuantifiedAssociationCollection::createFromNormalized(
                    [
                        'type2' => [
                            'product_models' => [],
                            'products' => [
                                ['quantity' => 0, 'identifier' => 'foo'],
                            ],
                        ],
                        'type1' => [
                            'product_models' => [
                                ['identifier' => 'other_sku', 'quantity' => 1],
                            ],
                            'products' => [
                                ['identifier' => 'foo', 'quantity' => 2],
                            ],
                        ],
                    ]
                );
        $this->assertSame(false, $this->sut->equals($differentCollection));
    }

    private function anIdMapping(): IdMapping
    {
            return IdMapping::createFromMapping(
                [
                    1 => 'entity_1',
                    2 => 'entity_2'
                ]
            );
        }

    private function aUuidMapping(): UuidMapping
    {
            return UuidMapping::createFromMapping([
                ['uuid' => '3f090f5e-3f54-4f34-879c-87779297d130', 'identifier' => 'entity_1', 'id' => 1],
                ['uuid' => '52254bba-a2c8-40bb-abe1-195e3970bd93', 'identifier' => 'entity_2', 'id' => 2],
                ['uuid' => '3aa5cfe1-83d0-4677-ae7f-d9d3c9f085b7', 'identifier' => null, 'id' => 3],
            ]);
        }

    private function anIncompleteUuidMapping(): UuidMapping
    {
            return UuidMapping::createFromMapping([
                ['uuid' => '3f090f5e-3f54-4f34-879c-87779297d130', 'identifier' => 'entity_1', 'id' => 1],
            ]);
        }

    private function anIncompleteIdMapping(): IdMapping
    {
            return IdMapping::createFromMapping(
                [
                    1 => 'entity_1'
                ]
            );
        }
}
