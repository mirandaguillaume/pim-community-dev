<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Model;

use Akeneo\Category\Infrastructure\Component\Classification\CategoryAwareInterface;
use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\UuidMapping;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductModelTest extends TestCase
{
    private ProductModel $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductModel();
    }

    public function test_it_is_updated_when_quantified_associations_are_updated(): void
    {
        $this->sut->patchQuantifiedAssociations(
                    [
                        'product_set' => [
                            'products' => [
                                ['identifier' => 'my_product', 'quantity' => 1],
                                ['identifier' => 'my_other_product', 'quantity' => 10],
                            ],
                            'product_models' => [
                                ['identifier' => 'model_tshirt', 'quantity' => 1],
                                ['identifier' => 'model_jeans', 'quantity' => 1],
                            ],
                        ],
                    ]
                );
        $this->sut->cleanup();
        $this->sut->patchQuantifiedAssociations(
                    [
                        'product_set' => [
                            'products' => [
                                ['identifier' => 'my_product', 'quantity' => 5],
                                ['identifier' => 'yet_another_product', 'quantity' => 2],
                            ],
                        ],
                    ]
                );
        $this->assertSame(true, $this->sut->isDirty());
    }

    public function test_it_is_not_updated_when_quantified_associations_are_not_updated(): void
    {
        $this->sut->patchQuantifiedAssociations(
                    [
                        'product_set' => [
                            'products' => [
                                ['identifier' => 'my_product', 'quantity' => 1],
                                ['identifier' => 'my_other_product', 'quantity' => 10],
                            ],
                            'product_models' => [
                                ['identifier' => 'model_tshirt', 'quantity' => 1],
                                ['identifier' => 'model_jeans', 'quantity' => 1],
                            ],
                        ],
                    ]
                );
        $this->sut->cleanup();
        $this->sut->patchQuantifiedAssociations(
                    [
                        'product_set' => [
                            'product_models' => [
                                ['identifier' => 'model_jeans', 'quantity' => 1],
                                ['identifier' => 'model_tshirt', 'quantity' => 1],
                            ],
                        ],
                    ]
                );
        $this->assertSame(false, $this->sut->isDirty());
    }

    public function test_it_is_updated_when_clearing_non_empty_quantified_associations(): void
    {
        $this->sut->patchQuantifiedAssociations(
                    [
                        'product_set' => [
                            'products' => [
                                ['identifier' => 'my_product', 'quantity' => 1],
                                ['identifier' => 'my_other_product', 'quantity' => 10],
                            ],
                            'product_models' => [
                                ['identifier' => 'model_tshirt', 'quantity' => 1],
                                ['identifier' => 'model_jeans', 'quantity' => 1],
                            ],
                        ],
                    ]
                );
        $this->sut->cleanup();
        $this->sut->clearQuantifiedAssociations();
        $this->assertSame(true, $this->sut->isDirty());
    }

    public function test_it_is_not_updated_when_clearing_empty_quantified_associations(): void
    {
        $this->sut->patchQuantifiedAssociations(
                    [
                        'product_set' => [
                            'products' => [],
                            'product_models' => [],
                        ],
                    ]
                );
        $this->sut->cleanup();
        $this->sut->clearQuantifiedAssociations();
        $this->assertSame(false, $this->sut->isDirty());
    }

    public function test_it_is_updated_when_merging_new_quantified_associations(): void
    {
        $this->sut->patchQuantifiedAssociations(
                    [
                        'associationB' => [
                            'products' => [
                                ['identifier' => 'my_product', 'quantity' => 1],
                            ],
                            'product_models' => [
                                ['identifier' => 'model_tshirt_1', 'quantity' => 3],
                                ['identifier' => 'model_tshirt_2', 'quantity' => 4],
                            ],
                        ],
                        'associationA' => [
                            'products' => [
                                ['identifier' => 'my_product', 'quantity' => 4],
                                ['identifier' => 'my_other_product', 'quantity' => 2],
                            ],
                            'product_models' => [
                                ['identifier' => 'model_tshirt_2', 'quantity' => 3],
                                ['identifier' => 'model_tshirt_1', 'quantity' => 1],
                            ],
                        ],
                    ]
                );
        $this->sut->cleanup();
        $this->sut->mergeQuantifiedAssociations(
                    QuantifiedAssociationCollection::createFromNormalized(
                        [
                            'associationB' => [
                                'products' => [
                                    ['identifier' => 'another_product', 'quantity' => 4],
                                ],
                            ],
                        ]
                    )
                );
        $this->assertSame(true, $this->sut->isDirty());
    }

    public function test_it_is_updated_when_merging_quantified_associations_with_an_updated_quantity(): void
    {
        $this->sut->patchQuantifiedAssociations(
                    [
                        'associationB' => [
                            'products' => [
                                ['identifier' => 'my_product', 'quantity' => 1],
                            ],
                            'product_models' => [
                                ['identifier' => 'model_tshirt_1', 'quantity' => 3],
                                ['identifier' => 'model_tshirt_2', 'quantity' => 4],
                            ],
                        ],
                        'associationA' => [
                            'products' => [
                                ['identifier' => 'my_product', 'quantity' => 4],
                                ['identifier' => 'my_other_product', 'quantity' => 2],
                            ],
                            'product_models' => [
                                ['identifier' => 'model_tshirt_2', 'quantity' => 3],
                                ['identifier' => 'model_tshirt_1', 'quantity' => 1],
                            ],
                        ],
                    ]
                );
        $this->sut->cleanup();
        $this->sut->mergeQuantifiedAssociations(
                    QuantifiedAssociationCollection::createFromNormalized(
                        [
                            'associationB' => [
                                'products' => [
                                    ['identifier' => 'my_product', 'quantity' => 20],
                                ],
                            ],
                        ]
                    )
                );
        $this->assertSame(true, $this->sut->isDirty());
    }

    public function test_it_is_not_updated_when_merging_identical_quantified_associations(): void
    {
        $this->sut->patchQuantifiedAssociations(
                    [
                        'associationB' => [
                            'products' => [
                                ['identifier' => 'my_product', 'quantity' => 1],
                            ],
                            'product_models' => [
                                ['identifier' => 'model_tshirt_2', 'quantity' => 3],
                                ['identifier' => 'model_tshirt_1', 'quantity' => 4],
                            ],
                        ],
                        'associationA' => [
                            'products' => [
                                ['identifier' => 'my_product', 'quantity' => 4],
                                ['identifier' => 'my_other_product', 'quantity' => 2],
                            ],
                            'product_models' => [
                                ['identifier' => 'model_tshirt_1', 'quantity' => 3],
                                ['identifier' => 'model_tshirt_2', 'quantity' => 1],
                            ],
                        ],
                    ]
                );
        $this->sut->cleanup();
        $this->sut->mergeQuantifiedAssociations(
                    QuantifiedAssociationCollection::createFromNormalized(
                        [
                            'associationA' => [
                                'products' => [
                                    ['identifier' => 'my_product', 'quantity' => 4],
                                ],
                            ],
                            'associationB' => [
                                'product_models' => [
                                    ['identifier' => 'model_tshirt_2', 'quantity' => 3],
                                ],
                            ],
                        ]
                    )
                );
        $this->assertSame(false, $this->sut->isDirty());
    }

    public function test_it_is_updated_when_filtering_associated_products_or_product_models_from_quantified_associations(): void
    {
        $this->sut->patchQuantifiedAssociations(
                    [
                        'associationB' => [
                            'products' => [
                                ['identifier' => 'my_product', 'quantity' => 1],
                            ],
                            'product_models' => [
                                ['identifier' => 'model_tshirt_2', 'quantity' => 3],
                                ['identifier' => 'model_tshirt_1', 'quantity' => 4],
                            ],
                        ],
                        'associationsA' => [
                            'products' => [
                                ['identifier' => 'my_product', 'quantity' => 4],
                                ['identifier' => 'my_other_product', 'quantity' => 2],
                            ],
                            'product_models' => [
                                ['identifier' => 'model_tshirt_1', 'quantity' => 3],
                                ['identifier' => 'model_tshirt_2', 'quantity' => 1],
                            ],
                        ],
                    ]
                );
        $this->sut->cleanup();
        $this->sut->filterQuantifiedAssociations(['my_product'], [], ['model_tshirt_2']);
        $this->assertSame(true, $this->sut->isDirty());
    }

    public function test_it_is_not_updated_when_keeping_all_associated_products_or_models(): void
    {
        $this->sut->patchQuantifiedAssociations(
                    [
                        'associationB' => [
                            'products' => [
                                ['identifier' => 'my_product', 'quantity' => 1],
                            ],
                            'product_models' => [
                                ['identifier' => 'model_tshirt_2', 'quantity' => 3],
                                ['identifier' => 'model_tshirt_1', 'quantity' => 4],
                            ],
                        ],
                        'associationsA' => [
                            'products' => [
                                ['identifier' => 'my_product', 'quantity' => 4],
                                ['identifier' => 'my_other_product', 'quantity' => 2],
                            ],
                            'product_models' => [
                                ['identifier' => 'model_tshirt_1', 'quantity' => 3],
                                ['identifier' => 'model_tshirt_2', 'quantity' => 1],
                            ],
                        ],
                    ]
                );
        $this->sut->cleanup();
        $this->sut->filterQuantifiedAssociations(
                    ['my_product', 'my_other_product'],
                    [],
                    ['model_tshirt_1', 'model_tshirt_2']
                );
        $this->assertSame(false, $this->sut->isDirty());
    }

    public function test_it_knows_if_it_has_an_association_for_a_given_type(): void
    {
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $this->assertSame(false, $this->sut->hasAssociationForTypeCode('x_sell'));
        $this->sut->addAssociation($xsellAssociation);
        $this->assertSame(true, $this->sut->hasAssociationForTypeCode('x_sell'));
    }

    public function test_it_adds_a_product_to_an_association(): void
    {
        $association = $this->createMock(AssociationInterface::class);

        $product = new Product();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $association->method('getAssociationType')->willReturn($xsellType);
        $association->method('hasProduct')->with($product)->willReturn(false);
        $association->method('getProducts')->willReturn(new ArrayCollection([]));
        $association->method('getProductModels')->willReturn(new ArrayCollection([]));
        $association->method('getGroups')->willReturn(new ArrayCollection([]));
        $association->method('setOwner')->with($this->sut)->willReturn($association);
        $this->sut->addAssociation($association);
        $association->expects($this->once())->method('addProduct')->with($product);
        $this->sut->addAssociatedProduct($product, 'x_sell');
    }

    public function test_it_is_updated_if_a_product_is_added_to_an_association(): void
    {
        $product = new Product();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $this->sut->addAssociation($xsellAssociation);
        $this->sut->cleanup();
        $this->sut->addAssociatedProduct($product, 'x_sell');
        $this->assertSame(true, $this->sut->isDirty());
    }

    public function test_it_is_not_updated_if_a_product_to_add_to_an_association_already_exists(): void
    {
        $product = new Product();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addProduct($product);
        $this->sut->addAssociation($xsellAssociation);
        $this->sut->cleanup();
        $this->sut->addAssociatedProduct($product, 'x_sell');
        $this->assertSame(false, $this->sut->isDirty());
    }

    public function test_it_removes_a_product_from_an_association(): void
    {
        $association = $this->createMock(AssociationInterface::class);

        $product = new Product();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $association->method('getAssociationType')->willReturn($xsellType);
        $association->method('hasProduct')->with($product)->willReturn(true);
        $association->method('getProducts')->willReturn(new ArrayCollection([$product]));
        $association->method('getProductModels')->willReturn(new ArrayCollection([]));
        $association->method('getGroups')->willReturn(new ArrayCollection([]));
        $association->method('setOwner')->with($this->sut)->willReturn($association);
        $this->sut->addAssociation($association);
        $association->expects($this->once())->method('removeProduct')->with($product);
        $this->sut->removeAssociatedProduct($product, 'x_sell');
    }

    public function test_it_is_updated_if_a_product_is_removed_from_an_association(): void
    {
        $product = new Product();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addProduct($product);
        $this->sut->addAssociation($xsellAssociation);
        $this->sut->cleanup();
        $this->sut->removeAssociatedProduct($product, 'x_sell');
        $this->assertSame(true, $this->sut->isDirty());
    }

    public function test_it_is_not_updated_if_a_product_to_remove_from_an_association_does_not_exist(): void
    {
        $product = new Product();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $this->sut->addAssociation($xsellAssociation);
        $this->sut->cleanup();
        $this->sut->removeAssociatedProduct($product, 'x_sell');
        $this->assertSame(false, $this->sut->isDirty());
    }

    public function test_it_returns_associated_products_in_terms_of_an_association_type(): void
    {
        $plate = new Product();
        $spoon = new Product();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addProduct($plate);
        $xsellAssociation->addProduct($spoon);
        $this->sut->addAssociation($xsellAssociation);
        $this->assertEquals(new ArrayCollection([$plate, $spoon]), $this->sut->getAssociatedProducts('x_sell'));
        $this->assertNull($this->sut->getAssociatedProducts('another_association_type'));
    }

    public function test_it_adds_a_product_model_to_an_association(): void
    {
        $association = $this->createMock(AssociationInterface::class);

        $productModel = new ProductModel();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $association->method('getAssociationType')->willReturn($xsellType);
        $association->method('hasProduct')->with($productModel)->willReturn(false);
        $association->method('getProducts')->willReturn(new ArrayCollection([]));
        $association->method('getProductModels')->willReturn(new ArrayCollection([]));
        $association->method('getGroups')->willReturn(new ArrayCollection([]));
        $association->method('setOwner')->with($this->sut)->willReturn($association);
        $this->sut->addAssociation($association);
        $association->expects($this->once())->method('addProductModel')->with($productModel);
        $this->sut->addAssociatedProductModel($productModel, 'x_sell');
    }

    public function test_it_is_updated_if_a_product_model_is_added_to_an_association(): void
    {
        $productModel = new ProductModel();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $this->sut->addAssociation($xsellAssociation);
        $this->sut->cleanup();
        $this->sut->addAssociatedProductModel($productModel, 'x_sell');
        $this->assertSame(true, $this->sut->isDirty());
    }

    public function test_it_is_not_updated_if_a_product_model_to_add_to_an_association_already_exists(): void
    {
        $productModel = new ProductModel();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addProductModel($productModel);
        $this->sut->addAssociation($xsellAssociation);
        $this->sut->cleanup();
        $this->sut->addAssociatedProductModel($productModel, 'x_sell');
        $this->assertSame(false, $this->sut->isDirty());
    }

    public function test_it_removes_a_product_model_from_an_association(): void
    {
        $association = $this->createMock(AssociationInterface::class);

        $productModel = new ProductModel();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $association->method('getAssociationType')->willReturn($xsellType);
        $association->method('getProducts')->willReturn(new ArrayCollection([]));
        $association->method('getProductModels')->willReturn(new ArrayCollection([$productModel]));
        $association->method('getGroups')->willReturn(new ArrayCollection([]));
        $association->method('setOwner')->with($this->sut)->willReturn($association);
        $this->sut->addAssociation($association);
        $association->expects($this->once())->method('removeProductModel')->with($productModel);
        $this->sut->removeAssociatedProductModel($productModel, 'x_sell');
    }

    public function test_it_is_updated_if_a_product_model_is_removed_from_an_association(): void
    {
        $productModel = new ProductModel();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addProductModel($productModel);
        $this->sut->addAssociation($xsellAssociation);
        $this->sut->cleanup();
        $this->sut->removeAssociatedProductModel($productModel, 'x_sell');
        $this->assertSame(true, $this->sut->isDirty());
    }

    public function test_it_is_not_updated_if_a_product_model_to_remove_from_an_association_does_not_exist(): void
    {
        $productModel = new ProductModel();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $this->sut->addAssociation($xsellAssociation);
        $this->sut->cleanup();
        $this->sut->removeAssociatedProductModel($productModel, 'x_sell');
        $this->assertSame(false, $this->sut->isDirty());
    }

    public function test_it_returns_associated_product_models_in_terms_of_an_association_type(): void
    {
        $plate = new ProductModel();
        $spoon = new ProductModel();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addProductModel($plate);
        $xsellAssociation->addProductModel($spoon);
        $this->sut->addAssociation($xsellAssociation);
        $this->assertEquals(new ArrayCollection([$plate, $spoon]), $this->sut->getAssociatedProductModels('x_sell'));
        $this->assertNull($this->sut->getAssociatedProductModels('another_association_type'));
    }

    public function test_it_adds_a_group_to_an_association(): void
    {
        $association = $this->createMock(AssociationInterface::class);

        $group = new Group();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $association->method('getAssociationType')->willReturn($xsellType);
        $association->method('getProducts')->willReturn(new ArrayCollection([]));
        $association->method('getProductModels')->willReturn(new ArrayCollection([]));
        $association->method('getGroups')->willReturn(new ArrayCollection([]));
        $association->method('setOwner')->with($this->sut)->willReturn($association);
        $this->sut->addAssociation($association);
        $association->expects($this->once())->method('addGroup')->with($group);
        $this->sut->addAssociatedGroup($group, 'x_sell');
    }

    public function test_it_is_updated_if_a_group_is_added_to_an_association(): void
    {
        $group = new Group();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $this->sut->addAssociation($xsellAssociation);
        $this->sut->cleanup();
        $this->sut->addAssociatedGroup($group, 'x_sell');
        $this->assertSame(true, $this->sut->isDirty());
    }

    public function test_it_is_not_updated_if_a_group_to_add_to_an_association_already_exists(): void
    {
        $group = new Group();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addGroup($group);
        $this->sut->addAssociation($xsellAssociation);
        $this->sut->cleanup();
        $this->sut->addAssociatedGroup($group, 'x_sell');
        $this->assertSame(false, $this->sut->isDirty());
    }

    public function test_it_removes_a_group_from_an_association(): void
    {
        $association = $this->createMock(AssociationInterface::class);

        $group = new Group();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $association->method('getAssociationType')->willReturn($xsellType);
        $association->method('getProducts')->willReturn(new ArrayCollection([]));
        $association->method('getProductModels')->willReturn(new ArrayCollection([]));
        $association->method('getGroups')->willReturn(new ArrayCollection([$group]));
        $association->method('setOwner')->with($this->sut)->willReturn($association);
        $this->sut->addAssociation($association);
        $association->expects($this->once())->method('removeGroup')->with($group);
        $this->sut->removeAssociatedGroup($group, 'x_sell');
    }

    public function test_it_is_updated_if_a_group_is_removed_from_an_association(): void
    {
        $group = new Group();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addGroup($group);
        $this->sut->addAssociation($xsellAssociation);
        $this->sut->cleanup();
        $this->sut->removeAssociatedGroup($group, 'x_sell');
        $this->assertSame(true, $this->sut->isDirty());
    }

    public function test_it_is_not_updated_if_a_group_to_remove_from_an_association_does_not_exist(): void
    {
        $group = new Group();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $this->sut->addAssociation($xsellAssociation);
        $this->sut->cleanup();
        $this->sut->removeAssociatedGroup($group, 'x_sell');
        $this->assertSame(false, $this->sut->isDirty());
    }

    public function test_it_returns_associated_groups_in_terms_of_an_association_type(): void
    {
        $plate = new Group();
        $spoon = new Group();
        $xsellAssociation = new ProductAssociation();
        $xsellType = new AssociationType();
        $xsellType->setCode('x_sell');
        $xsellAssociation->setAssociationType($xsellType);
        $xsellAssociation->addGroup($plate);
        $xsellAssociation->addGroup($spoon);
        $this->sut->addAssociation($xsellAssociation);
        $this->assertEquals(new ArrayCollection([$plate, $spoon]), $this->sut->getAssociatedGroups('x_sell'));
        $this->assertNull($this->sut->getAssociatedGroups('another_association_type'));
    }

    private function someRawQuantifiedAssociations(): array
    {
            return [
                'PACK' => [
                    'products'       => [
                        ['id' => 1, 'quantity' => 1],
                        ['id' => 2, 'quantity' => 2]
                    ],
                    'product_models' => [
                        ['id' => 1, 'quantity' => 1],
                        ['id' => 2, 'quantity' => 2]
                    ],
                ]
            ];
        }

    private function idMapping(): IdMapping
    {
            return IdMapping::createFromMapping([1 => 'entity_1', 2 => 'entity_2']);
        }

    private function uuidMapping(): UuidMapping
    {
            return UuidMapping::createFromMapping([
                ['uuid' => self::UUID1, 'id' => 1, 'identifier' => 'entity_1'],
                ['uuid' => self::UUID2, 'id' => 2, 'identifier' => 'entity_2'],
            ]);
        }
}
