<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProductUuids;
use Akeneo\Pim\Enrichment\Product\Application\Applier\AssociationUserIntentCollectionApplier;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProductModels;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProducts;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpParser\Node\Arg;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationUserIntentCollectionApplierTest extends TestCase
{
    private ObjectUpdaterInterface|MockObject $productUpdater;
    private GetViewableProducts|MockObject $getViewableProducts;
    private GetViewableProductModels|MockObject $getViewableProductModels;
    private AssociationUserIntentCollectionApplier $sut;

    protected function setUp(): void
    {
        $this->productUpdater = $this->createMock(ObjectUpdaterInterface::class);
        $this->getViewableProducts = $this->createMock(GetViewableProducts::class);
        $this->getViewableProductModels = $this->createMock(GetViewableProductModels::class);
        $this->sut = new AssociationUserIntentCollectionApplier($this->productUpdater, $this->getViewableProducts, $this->getViewableProductModels);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AssociationUserIntentCollectionApplier::class, $this->sut);
        $this->assertInstanceOf(UserIntentApplier::class, $this->sut);
    }

    public function test_it_supports_association_user_intent_collection(): void
    {
        $this->assertSame([AssociationUserIntentCollection::class], $this->sut->getSupportedUserIntents());
    }

    public function test_it_applies_associate_products(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $associatedProduct = new Product();
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'baz'));
        $product->expects($this->once())->method('getAssociatedProducts')->with('X_SELL')->willReturn(new ArrayCollection([$associatedProduct]));
        $collection = new AssociationUserIntentCollection([
                    new AssociateProducts('X_SELL', ['foo', 'bar']),
                ]);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['associations' => [
                    'X_SELL' => [
                        'products' => ['baz', 'foo', 'bar'],
                    ],
                ]]);
        $this->sut->apply($collection, $product, 42);
    }

    public function test_it_applies_multiple_associate_products(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $associatedProduct = new Product();
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'baz'));
        $product->expects($this->once())->method('getAssociatedProducts')->with('X_SELL')->willReturn(new ArrayCollection([$associatedProduct]));
        $product->expects($this->once())->method('getAssociatedProducts')->with('UPSELL')->willReturn(new ArrayCollection([$associatedProduct]));
        $collection = new AssociationUserIntentCollection([
                    new AssociateProducts('X_SELL', ['foo', 'bar']),
                    new AssociateProducts('UPSELL', ['foo', 'bar']),
                ]);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['associations' => [
                    'X_SELL' => [
                        'products' => ['baz', 'foo', 'bar'],
                    ],
                    'UPSELL' => [
                        'products' => ['baz', 'foo', 'bar'],
                    ],
                ]]);
        $this->sut->apply($collection, $product, 42);
    }

    public function test_it_applies_multiple_same_associate_products(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $associatedProduct = new Product();
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'baz'));
        $product->expects($this->exactly(2))->method('getAssociatedProducts')->with('X_SELL')->willReturn(new ArrayCollection([$associatedProduct]));
        $collection = new AssociationUserIntentCollection([
                    new AssociateProducts('X_SELL', ['foo', 'bar']),
                    new AssociateProducts('X_SELL', ['foo', 'toto']),
                ]);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['associations' => [
                    'X_SELL' => [
                        'products' => ['baz', 'foo', 'bar', 'toto'],
                    ],
                ]]);
        $this->sut->apply($collection, $product, 42);
    }

    public function test_it_does_nothing_if_products_are_already_associated(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $associatedProduct = new Product();
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'baz'));
        $product->expects($this->once())->method('getAssociatedProducts')->with('X_SELL')->willReturn(new ArrayCollection([$associatedProduct]));
        $collection = new AssociationUserIntentCollection([
                    new AssociateProducts('X_SELL', ['baz']),
                ]);
        $this->productUpdater->expects($this->never())->method('update');
        $this->sut->apply($collection, $product, 42);
    }

    public function test_it_applies_dissociate_products(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $associatedProducts = [];
        $associatedProduct = new Product();
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'baz'));
        $associatedProducts[] = $associatedProduct;
        $associatedProduct = new Product();
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'qux'));
        $associatedProducts[] = $associatedProduct;
        $product->expects($this->once())->method('getAssociatedProducts')->with('X_SELL')->willReturn(new ArrayCollection($associatedProducts));
        $collection = new AssociationUserIntentCollection([
                    new DissociateProducts('X_SELL', ['baz']),
                ]);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['associations' => [
                    'X_SELL' => [
                        'products' => ['qux'],
                    ],
                ]]);
        $this->sut->apply($collection, $product, 42);
    }

    public function test_it_does_nothing_if_product_to_dissociate_is_not_associated(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $associatedProduct = new Product();
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'baz'));
        $product->expects($this->once())->method('getAssociatedProducts')->with('X_SELL')->willReturn(new ArrayCollection([$associatedProduct]));
        $collection = new AssociationUserIntentCollection([
                    new DissociateProducts('X_SELL', ['qux']),
                ]);
        $this->productUpdater->expects($this->never())->method('update');
        $this->sut->apply($collection, $product, 42);
    }

    public function test_it_associates_and_dissociates_products(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $associatedProduct = new Product();
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'baz'));
        $product->expects($this->exactly(2))->method('getAssociatedProducts')->with('X_SELL')->willReturn(new ArrayCollection([$associatedProduct]));
        $collection = new AssociationUserIntentCollection([
                    new AssociateProducts('X_SELL', ['qux']),
                    new DissociateProducts('X_SELL', ['baz']),
                ]);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['associations' => [
                    'X_SELL' => [
                        'products' => ['qux'],
                    ],
                ]]);
        $this->sut->apply($collection, $product, 42);
    }

    public function test_it_replaces_associated_products(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $associatedProducts = [];
        $associatedProduct = new Product();
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'baz'));
        $associatedProducts[] = $associatedProduct;
        $associatedProduct = new Product();
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'non_viewable_product'));
        $associatedProducts[] = $associatedProduct;
        $product->expects($this->once())->method('getAssociatedProducts')->with('X_SELL')->willReturn(new ArrayCollection($associatedProducts));
        $collection = new AssociationUserIntentCollection([
                    new ReplaceAssociatedProducts('X_SELL', ['quux', 'quuz', 'corge']),
                ]);
        // product is updated with new values and non viewable product identifiers
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['associations' => [
            'X_SELL' => [
                'products' => ['non_viewable_product', 'quux', 'quuz', 'corge'],
            ],
        ]]);
        $this->getViewableProducts->expects($this->once())->method('fromProductIdentifiers')->with(['baz', 'non_viewable_product'], 42)->willReturn(['baz']);
        $this->sut->apply($collection, $product, 42);
    }

    public function test_it_replaces_associated_products_with_uuids(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $viewableProduct = new Product();
        $nonViewableProduct = new Product();
        $associatedProducts = [$viewableProduct, $nonViewableProduct];
        $product->expects($this->once())->method('getAssociatedProducts')->with('X_SELL')->willReturn(new ArrayCollection($associatedProducts));
        $newAssociatedProductUuid1 = Uuid::uuid4()->toString();
        $newAssociatedProductUuid2 = Uuid::uuid4()->toString();
        $newAssociatedProductUuid3 = Uuid::uuid4()->toString();
        $collection = new AssociationUserIntentCollection([
                    new ReplaceAssociatedProductUuids('X_SELL', [$newAssociatedProductUuid1, $newAssociatedProductUuid2, $newAssociatedProductUuid3]),
                ]);
        // product is updated with new values and non viewable product identifiers
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['associations' => [
            'X_SELL' => [
                'product_uuids' => [$nonViewableProduct->getUuid()->toString(), $newAssociatedProductUuid1, $newAssociatedProductUuid2, $newAssociatedProductUuid3],
            ],
        ]]);
        $uuids = [$viewableProduct->getUuid()->toString(), $nonViewableProduct->getUuid()->toString()];
        \sort($uuids);
        $uuids = array_map(fn (string $uuid): UuidInterface => Uuid::fromString($uuid), $uuids);
        $this->getViewableProducts->method('fromProductUuids')->with($uuids, 42)->willReturn([$viewableProduct->getUuid()]);
        $this->sut->apply($collection, $product, 42);
    }

    public function test_it_does_nothing_if_products_to_associate_are_the_same_as_existing_associated_products(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $associatedProducts = [];
        $associatedProduct = new Product();
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'baz'));
        $associatedProducts[] = $associatedProduct;
        $associatedProduct = new Product();
        $associatedProduct->addValue(IdentifierValue::value('sku', true, 'qux'));
        $associatedProducts[] = $associatedProduct;
        $product->expects($this->once())->method('getAssociatedProducts')->with('X_SELL')->willReturn(new ArrayCollection($associatedProducts));
        $collection = new AssociationUserIntentCollection([
                    new ReplaceAssociatedProducts('X_SELL', ['qux', 'baz']),
                ]);
        $this->productUpdater->expects($this->never())->method('update');
        $this->sut->apply($collection, $product, 42);
    }

    public function test_it_applies_associate_product_models(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $associatedProductModel = new ProductModel();
        $associatedProductModel->setCode('foo');
        $product->expects($this->once())->method('getAssociatedProductModels')->with('X_SELL')->willReturn(new ArrayCollection([$associatedProductModel]));
        $collection = new AssociationUserIntentCollection([
                    new AssociateProductModels('X_SELL', ['bar', 'baz']),
                ]);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['associations' => [
                    'X_SELL' => [
                        'product_models' => ['foo', 'bar', 'baz'],
                    ],
                ]]);
        $this->sut->apply($collection, $product, 42);
    }

    public function test_it_does_nothing_if_product_models_are_already_associated(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $associatedProductModel = new ProductModel();
        $associatedProductModel->setCode('foo');
        $product->expects($this->once())->method('getAssociatedProductModels')->with('X_SELL')->willReturn(new ArrayCollection([$associatedProductModel]));
        $collection = new AssociationUserIntentCollection([
                    new AssociateProductModels('X_SELL', ['foo']),
                ]);
        $this->productUpdater->expects($this->never())->method('update');
        $this->sut->apply($collection, $product, 42);
    }

    public function test_it_applies_dissociate_product_models(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $associatedProductModel1 = new ProductModel();
        $associatedProductModel1->setCode('foo');
        $associatedProductModel2 = new ProductModel();
        $associatedProductModel2->setCode('bar');
        $associatedProductModels = [$associatedProductModel1, $associatedProductModel2];
        $product->expects($this->once())->method('getAssociatedProductModels')->with('X_SELL')->willReturn(new ArrayCollection($associatedProductModels));
        $collection = new AssociationUserIntentCollection([
                    new DissociateProductModels('X_SELL', ['foo']),
                ]);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['associations' => [
                    'X_SELL' => [
                        'product_models' => ['bar'],
                    ],
                ]]);
        $this->sut->apply($collection, $product, 42);
    }

    public function test_it_does_nothing_if_product_model_to_dissociate_is_not_associated(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $associatedProductModel = new ProductModel();
        $associatedProductModel->setCode('baz');
        $product->expects($this->once())->method('getAssociatedProductModels')->with('X_SELL')->willReturn(new ArrayCollection([$associatedProductModel]));
        $collection = new AssociationUserIntentCollection([
                    new DissociateProductModels('X_SELL', ['not_associated_model_code']),
                ]);
        $this->productUpdater->expects($this->never())->method('update');
        $this->sut->apply($collection, $product, 42);
    }

    public function test_it_replaces_associated_product_models(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $associatedProductModel1 = new ProductModel();
        $associatedProductModel1->setCode('viewable_product_model');
        $associatedProductModel2 = new ProductModel();
        $associatedProductModel2->setCode('non_viewable_product_model');
        $associatedProductModels = [$associatedProductModel1, $associatedProductModel2];
        $product->expects($this->once())->method('getAssociatedProductModels')->with('X_SELL')->willReturn(new ArrayCollection($associatedProductModels));
        $collection = new AssociationUserIntentCollection([
                    new ReplaceAssociatedProductModels('X_SELL', ['quux', 'quuz', 'corge']),
                ]);
        $this->getViewableProductModels->expects($this->once())->method('fromProductModelCodes')->with(['non_viewable_product_model', 'viewable_product_model'], 42)->willReturn(['viewable_product_model']);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['associations' => [
                    'X_SELL' => [
                        'product_models' => ['non_viewable_product_model', 'quux', 'quuz', 'corge'],
                    ],
                ]]);
        $this->sut->apply($collection, $product, 42);
    }

    public function test_it_does_nothing_if_product_models_to_associate_are_the_same_as_existing_associated_product_models(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $associatedProductModel1 = new ProductModel();
        $associatedProductModel1->setCode('foo');
        $associatedProductModel2 = new ProductModel();
        $associatedProductModel2->setCode('bar');
        $associatedProductModels = [$associatedProductModel1, $associatedProductModel2];
        $product->expects($this->once())->method('getAssociatedProductModels')->with('X_SELL')->willReturn(new ArrayCollection($associatedProductModels));
        $collection = new AssociationUserIntentCollection([
                    new ReplaceAssociatedProductModels('X_SELL', ['foo', 'bar']),
                ]);
        $this->getViewableProductModels->expects($this->never())->method('fromProductModelCodes');
        $this->productUpdater->expects($this->never())->method('update');
        $this->sut->apply($collection, $product, 42);
    }

    public function test_it_applies_associate_groups(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $group = $this->createMock(GroupInterface::class);

        $group->method('getCode')->willReturn('group1');
        $product->expects($this->once())->method('getAssociatedGroups')->with('X_SELL')->willReturn(new ArrayCollection([$group]));
        $collection = new AssociationUserIntentCollection([
                    new AssociateGroups('X_SELL', ['group2', 'group3']),
                ]);
        $this->productUpdater->expects($this->once())->method('update')->with($product, ['associations' => [
                    'X_SELL' => [
                        'groups' => ['group1', 'group2', 'group3'],
                    ],
                ]]);
        $this->sut->apply($collection, $product, 42);
    }

    public function test_it_does_nothing_if_groups_are_already_associated(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $group = new Group();
        $group->setCode('group1');
        $product->expects($this->once())->method('getAssociatedGroups')->with('X_SELL')->willReturn(new ArrayCollection([$group]));
        $collection = new AssociationUserIntentCollection([
                    new AssociateGroups('X_SELL', ['group1']),
                ]);
        $this->productUpdater->expects($this->never())->method('update');
        $this->sut->apply($collection, $product, 42);
    }
}
