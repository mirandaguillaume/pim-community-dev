<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\Domain\StandardFormat\Validator\QuantifiedAssociationsStructureValidator;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\QuantifiedAssociationUserIntentFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuantifiedAssociationUserIntentFactoryTest extends TestCase
{
    private QuantifiedAssociationsStructureValidator|MockObject $quantifiedAssociationsStructureValidator;
    private QuantifiedAssociationUserIntentFactory $sut;

    protected function setUp(): void
    {
        $this->quantifiedAssociationsStructureValidator = $this->createMock(QuantifiedAssociationsStructureValidator::class);
        $this->sut = new QuantifiedAssociationUserIntentFactory($this->quantifiedAssociationsStructureValidator);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(QuantifiedAssociationUserIntentFactory::class, $this->sut);
    }

    public function test_it_returns_quantified_association_user_intents(): void
    {
        $this->assertEquals([
                    new ReplaceAssociatedQuantifiedProducts('QUANTIFIED_ASS', [
                        new QuantifiedEntity('identifier1', 10),
                        new QuantifiedEntity('identifier2', 20),
                    ]),
                    new ReplaceAssociatedQuantifiedProductModels('QUANTIFIED_ASS', [
                        new QuantifiedEntity('code1', 20),
                        new QuantifiedEntity('code2', 10),
                    ]),
                    new ReplaceAssociatedQuantifiedProducts('123', [
                        new QuantifiedEntity('foo', 2),
                    ]),
                    new ReplaceAssociatedQuantifiedProductModels('123', [
                        new QuantifiedEntity('bar', 5),
                    ]),
                    new ReplaceAssociatedQuantifiedProductUuids('another', [
                        new QuantifiedEntity('b8f895c5-330a-4d6d-9a74-78db307633bd', 2),
                    ]),
                    new ReplaceAssociatedQuantifiedProductModels('another', [
                        new QuantifiedEntity('bar', 5),
                    ]),
                ], $this->sut->create('quantified_associations', [
                    'QUANTIFIED_ASS' => [
                        'products' => [
                            ['identifier' => 'identifier1', 'quantity' => 10],
                            ['identifier' => 'identifier2', 'quantity' => 20],
                        ],
                        'product_models' => [
                            ['identifier' => 'code1', 'quantity' => 20],
                            ['identifier' => 'code2', 'quantity' => 10],
                        ],
                    ],
                    '123' => [
                        'products' => [
                            ['identifier' => 'foo', 'quantity' => 2],
                        ],
                        'product_models' => [
                            ['identifier' => 'bar', 'quantity' => 5],
                        ],
                    ],
                    'another' => [
                        'product_uuids' => [
                            ['uuid' => 'b8f895c5-330a-4d6d-9a74-78db307633bd', 'quantity' => 2],
                        ],
                        'product_models' => [
                            ['identifier' => 'bar', 'quantity' => 5],
                        ],
                    ],
                ]));
    }
}
