<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\Application\Applier\QuantifiedAssociationUserIntentCollectionApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProductModels;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProducts;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class QuantifiedAssociationUserIntentCollectionApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $productUpdater;
    private GetViewableProducts|MockObject $getViewableProducts;
    private GetViewableProductModels|MockObject $getViewableProductModels;
    private QuantifiedAssociationUserIntentCollectionApplier $sut;

    protected function setUp(): void
    {
        $this->productUpdater = $this->createMock(ObjectUpdaterInterface::class);
        $this->getViewableProducts = $this->createMock(GetViewableProducts::class);
        $this->getViewableProductModels = $this->createMock(GetViewableProductModels::class);
        $this->sut = new QuantifiedAssociationUserIntentCollectionApplier($this->productUpdater, $this->getViewableProducts, $this->getViewableProductModels);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(QuantifiedAssociationUserIntentCollectionApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_associates_quantified_products_by_updating_a_quantity(): void
    {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
                    'bundle' => [
                        'products' => [
                            ['identifier' => 'foo', 'uuid' => '04cc1240-e68b-4350-a829-097e5cedd7cd', 'quantity' => 2],
                            ['identifier' => 'bar', 'uuid' => 'ae639bdc-cc03-4961-9e28-7e6a2e3a6623', 'quantity' => 4],
                        ],
                    ],
                ]));
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['quantified_associations' => [
                    'bundle' => [
                        'products' => [
                            ['identifier' => 'foo', 'uuid' => '04cc1240-e68b-4350-a829-097e5cedd7cd', 'quantity' => 8],
                            ['identifier' => 'bar', 'uuid' => 'ae639bdc-cc03-4961-9e28-7e6a2e3a6623', 'quantity' => 4],
                        ],
                    ],
                ]]);
        $this->sut->apply(
            new QuantifiedAssociationUserIntentCollection([
                        new AssociateQuantifiedProducts('bundle', [new QuantifiedEntity('foo', 8)]),
                    ]),
            $product,
            10
        );
    }

    public function test_it_associates_quantified_products_by_adding_one_association(): void
    {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
                    'bundle' => [
                        'products' => [
                            ['identifier' => 'foo', 'uuid' => '04cc1240-e68b-4350-a829-097e5cedd7cd', 'quantity' => 2],
                            ['identifier' => 'bar', 'uuid' => 'ae639bdc-cc03-4961-9e28-7e6a2e3a6623', 'quantity' => 4],
                        ],
                    ],
                ]));
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['quantified_associations' => [
                    'bundle' => [
                        'products' => [
                            ['identifier' => 'foo', 'uuid' => '04cc1240-e68b-4350-a829-097e5cedd7cd', 'quantity' => 8],
                            ['identifier' => 'bar', 'uuid' => 'ae639bdc-cc03-4961-9e28-7e6a2e3a6623', 'quantity' => 4],
                            ['identifier' => 'baz', 'uuid' => null, 'quantity' => 3],
                        ],
                    ],
                ]]);
        $this->sut->apply(
            new QuantifiedAssociationUserIntentCollection([
                        new AssociateQuantifiedProducts('bundle', [new QuantifiedEntity('foo', 8), new QuantifiedEntity('baz', 3)]),
                    ]),
            $product,
            10
        );
    }

    public function test_it_does_nothing_when_product_is_already_associated_with_the_same_quantity(): void
    {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
                    'bundle' => [
                        'products' => [
                            ['identifier' => 'foo', 'uuid' => '04cc1240-e68b-4350-a829-097e5cedd7cd', 'quantity' => 2],
                            ['identifier' => 'bar', 'uuid' => 'ae639bdc-cc03-4961-9e28-7e6a2e3a6623', 'quantity' => 4],
                        ],
                    ],
                ]));
        $this->productUpdater->expects($this->never())->method('update');
        $this->sut->apply(
            new QuantifiedAssociationUserIntentCollection([
                        new AssociateQuantifiedProducts('bundle', [new QuantifiedEntity('bar', 4)]),
                    ]),
            $product,
            10
        );
    }

    public function test_it_dissociates_quantified_products(): void
    {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
                    'bundle' => [
                        'products' => [
                            ['identifier' => 'foo', 'uuid' => '04cc1240-e68b-4350-a829-097e5cedd7cd', 'quantity' => 2],
                            ['identifier' => 'bar', 'uuid' => 'ae639bdc-cc03-4961-9e28-7e6a2e3a6623', 'quantity' => 4],
                        ],
                    ],
                ]));
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['quantified_associations' => [
                    'bundle' => [
                        'products' => [
                            ['identifier' => 'bar', 'uuid' => 'ae639bdc-cc03-4961-9e28-7e6a2e3a6623', 'quantity' => 4],
                        ],
                    ],
                ]]);
        $this->sut->apply(
            new QuantifiedAssociationUserIntentCollection([
                        new DissociateQuantifiedProducts('bundle', ['foo', 'baz']),
                    ]),
            $product,
            10
        );
    }

    public function test_it_dissociates_all_quantified_products(): void
    {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
                    'bundle' => [
                        'products' => [
                            ['identifier' => 'foo', 'uuid' => '04cc1240-e68b-4350-a829-097e5cedd7cd', 'quantity' => 2],
                            ['identifier' => 'bar', 'uuid' => 'ae639bdc-cc03-4961-9e28-7e6a2e3a6623', 'quantity' => 4],
                        ],
                    ],
                ]));
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['quantified_associations' => [
                    'bundle' => [
                        'products' => [],
                    ],
                ]]);
        $this->sut->apply(
            new QuantifiedAssociationUserIntentCollection([
                        new DissociateQuantifiedProducts('bundle', ['foo', 'bar']),
                    ]),
            $product,
            10
        );
    }

    public function test_it_replaces_quantified_products(): void
    {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
                    'bundle' => [
                        'products' => [
                            ['identifier' => 'foo', 'uuid' => '04cc1240-e68b-4350-a829-097e5cedd7cd', 'quantity' => 2],
                            ['identifier' => 'bar', 'uuid' => 'ae639bdc-cc03-4961-9e28-7e6a2e3a6623', 'quantity' => 4],
                            ['identifier' => 'baz', 'uuid' => '70baf7a0-a8f0-427c-9937-4ca06ec6e484', 'quantity' => 5],
                        ],
                    ],
                ]));
        $this->getViewableProducts->method('fromProductIdentifiers')->with(['bar', 'baz', 'foo'], 10)->willReturn(['bar', 'foo']);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['quantified_associations' => [
                    'bundle' => [
                        'products' => [
                            ['identifier' => 'foo', 'uuid' => null, 'quantity' => 8],
                            ['identifier' => 'baz', 'uuid' => '70baf7a0-a8f0-427c-9937-4ca06ec6e484', 'quantity' => 5],
                        ],
                    ],
                ]]);
        $this->sut->apply(
            new QuantifiedAssociationUserIntentCollection([
                        new ReplaceAssociatedQuantifiedProducts('bundle', [
                            new QuantifiedEntity('foo', 8),
                        ]),
                    ]),
            $product,
            10
        );
    }

    public function test_it_replaces_quantified_products_by_uuid(): void
    {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
                    'bundle' => [
                        'products' => [
                            ['identifier' => 'foo', 'uuid' => '04cc1240-e68b-4350-a829-097e5cedd7cd', 'quantity' => 2],
                            ['identifier' => 'bar', 'uuid' => 'ae639bdc-cc03-4961-9e28-7e6a2e3a6623', 'quantity' => 4],
                            ['identifier' => 'baz', 'uuid' => '70baf7a0-a8f0-427c-9937-4ca06ec6e484', 'quantity' => 5],
                        ],
                    ],
                ]));
        $fooUuid = Uuid::fromString('04cc1240-e68b-4350-a829-097e5cedd7cd');
        $barUuid = Uuid::fromString('ae639bdc-cc03-4961-9e28-7e6a2e3a6623');
        $bazUuid = Uuid::fromString('70baf7a0-a8f0-427c-9937-4ca06ec6e484');
        $this->getViewableProducts->method('fromProductUuids')->with([$fooUuid, $bazUuid, $barUuid], 10)->willReturn([$fooUuid, $barUuid]);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['quantified_associations' => [
                    'bundle' => [
                        'products' => [
                            ['identifier' => null, 'uuid' => '04cc1240-e68b-4350-a829-097e5cedd7cd', 'quantity' => 8],
                            ['identifier' => 'baz', 'uuid' => '70baf7a0-a8f0-427c-9937-4ca06ec6e484', 'quantity' => 5],
                        ],
                    ],
                ]]);
        $this->sut->apply(
            new QuantifiedAssociationUserIntentCollection([
                        new ReplaceAssociatedQuantifiedProductUuids('bundle', [
                            new QuantifiedEntity('04cc1240-e68b-4350-a829-097e5cedd7cd', 8),
                        ]),
                    ]),
            $product,
            10
        );
    }

    public function test_it_associates_quantified_product_models_by_updating_a_quantity(): void
    {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
                    'bundle' => [
                        'product_models' => [
                            ['identifier' => 'foo', 'quantity' => 2],
                            ['identifier' => 'bar', 'quantity' => 4],
                        ],
                    ],
                ]));
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['quantified_associations' => [
                    'bundle' => [
                        'product_models' => [
                            ['identifier' => 'foo', 'quantity' => 8],
                            ['identifier' => 'bar', 'quantity' => 4],
                        ],
                    ],
                ]]);
        $this->sut->apply(
            new QuantifiedAssociationUserIntentCollection([
                        new AssociateQuantifiedProductModels('bundle', [new QuantifiedEntity('foo', 8)]),
                    ]),
            $product,
            10
        );
    }

    public function test_it_dissociates_quantified_product_models(): void
    {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
                    'bundle' => [
                        'product_models' => [
                            ['identifier' => 'foo', 'quantity' => 2],
                            ['identifier' => 'bar', 'quantity' => 4],
                        ],
                    ],
                ]));
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['quantified_associations' => [
                    'bundle' => [
                        'product_models' => [
                            ['identifier' => 'bar', 'quantity' => 4],
                        ],
                    ],
                ]]);
        $this->sut->apply(
            new QuantifiedAssociationUserIntentCollection([
                        new DissociateQuantifiedProductModels('bundle', ['foo', 'baz']),
                    ]),
            $product,
            10
        );
    }

    public function test_it_replaces_quantified_product_models(): void
    {
        $product = new Product();
        $product->mergeQuantifiedAssociations(QuantifiedAssociationCollection::createFromNormalized([
                    'bundle' => [
                        'product_models' => [
                            ['identifier' => 'foo', 'quantity' => 2],
                            ['identifier' => 'bar', 'quantity' => 4],
                            ['identifier' => 'baz', 'quantity' => 5],
                        ],
                    ],
                ]));
        $this->getViewableProductModels->method('fromProductModelCodes')->with(['bar', 'baz', 'foo'], 10)->willReturn(['bar', 'foo']);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['quantified_associations' => [
                    'bundle' => [
                        'product_models' => [
                            ['identifier' => 'foo', 'quantity' => 8],
                            ['identifier' => 'baz', 'quantity' => 5],
                        ],
                    ],
                ]]);
        $this->sut->apply(
            new QuantifiedAssociationUserIntentCollection([
                        new ReplaceAssociatedQuantifiedProductModels('bundle', [
                            new QuantifiedEntity('foo', 8),
                        ]),
                    ]),
            $product,
            10
        );
    }
}
