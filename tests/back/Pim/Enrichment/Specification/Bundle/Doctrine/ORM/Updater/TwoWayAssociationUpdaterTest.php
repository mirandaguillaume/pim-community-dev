<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Doctrine\ORM\Updater;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Updater\TwoWayAssociationUpdater;
use Akeneo\Pim\Enrichment\Component\Product\Association\MissingAssociationAdder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\TwoWayAssociationWithTheSameProductException;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Updater\TwoWayAssociationUpdaterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class TwoWayAssociationUpdaterTest extends TestCase
{
    private MissingAssociationAdder|MockObject $missingAssociationAdder;
    private ManagerRegistry|MockObject $registry;
    private EntityManager|MockObject $entityManager;
    private TwoWayAssociationUpdater $sut;

    protected function setUp(): void
    {
        $this->missingAssociationAdder = $this->createMock(MissingAssociationAdder::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->sut = new TwoWayAssociationUpdater($this->registry, $this->missingAssociationAdder);
        $this->registry->method('getManager')->willReturn($this->entityManager);
    }

    public function test_it_is_a_two_way_association_updater(): void
    {
        $this->assertInstanceOf(TwoWayAssociationUpdater::class, $this->sut);
        $this->assertInstanceOf(TwoWayAssociationUpdaterInterface::class, $this->sut);
    }

    public function test_it_adds_missing_association_and_associates_the_product(): void
    {
        $associatedProduct = $this->createMock(ProductInterface::class);

        $owner = new Product();
        $owner->setIdentifier('product_identifier');
        $associatedProduct->method('getIdentifier')->willReturn('associated_product_identifier');
        $associatedProduct->method('getUuid')->willReturn(Uuid::uuid4());
        $associatedProduct->method('hasAssociationForTypeCode')->with('xsell')->willReturn(false);
        $associatedProduct->method('getAssociatedProducts')->with('xsell')->willReturn(new ArrayCollection());
        $this->missingAssociationAdder->expects($this->once())->method('addMissingAssociations')->with($associatedProduct);
        $associatedProduct->expects($this->once())->method('addAssociatedProduct')->with($owner, 'xsell');
        $this->sut->createInversedAssociation($owner, 'xsell', $associatedProduct);
    }

    public function test_it_associates_a_product(): void
    {
        $associatedProduct = $this->createMock(ProductInterface::class);

        $owner = new Product();
        $owner->setIdentifier('owner');
        $clonedOwner = new Product($owner->getUuid()->toString());
        $clonedOwner->setIdentifier('owner');
        $associatedProduct->method('getIdentifier')->willReturn('associated_product_identifier');
        $associatedProduct->method('getUuid')->willReturn(Uuid::uuid4());
        $associatedProduct->method('hasAssociationForTypeCode')->with('xsell')->willReturn(true);
        $associatedProduct->method('getAssociatedProducts')->with('xsell')->willReturn(new ArrayCollection([$clonedOwner]));
        $associatedProduct->expects($this->once())->method('removeAssociatedProduct')->with($clonedOwner, 'xsell');
        $associatedProduct->expects($this->once())->method('addAssociatedProduct')->with($owner, 'xsell');
        $this->sut->createInversedAssociation($owner, 'xsell', $associatedProduct);
    }

    public function test_it_adds_missing_association_and_associates_the_product_model(): void
    {
        $associatedProduct = $this->createMock(ProductInterface::class);

        $owner = new ProductModel();
        $associatedProduct->method('hasAssociationForTypeCode')->with('xsell')->willReturn(false);
        $associatedProduct->method('getAssociatedProductModels')->with('xsell')->willReturn(new ArrayCollection());
        $this->missingAssociationAdder->expects($this->once())->method('addMissingAssociations')->with($associatedProduct);
        $associatedProduct->expects($this->once())->method('addAssociatedProductModel')->with($owner, 'xsell');
        $this->sut->createInversedAssociation($owner, 'xsell', $associatedProduct);
    }

    public function test_it_associates_a_product_model(): void
    {
        $associatedProduct = $this->createMock(ProductInterface::class);

        $owner = new ProductModel();
        $owner->setCode('owner');
        $clonedOwner = new ProductModel();
        $clonedOwner->setCode('owner');
        $associatedProduct->method('hasAssociationForTypeCode')->with('xsell')->willReturn(true);
        $associatedProduct->method('getAssociatedProductModels')->with('xsell')->willReturn(new ArrayCollection([$clonedOwner]));
        $associatedProduct->expects($this->once())->method('removeAssociatedProductModel')->with($clonedOwner, 'xsell');
        $associatedProduct->expects($this->once())->method('addAssociatedProductModel')->with($owner, 'xsell');
        $this->sut->createInversedAssociation($owner, 'xsell', $associatedProduct);
    }

    public function test_it_associates_only_product_or_product_model(): void
    {
        $associatedProduct = $this->createMock(ProductInterface::class);
        $owner = $this->createMock(EntityWithAssociationsInterface::class);

        $associatedProduct->method('hasAssociationForTypeCode')->with('xsell')->willReturn(true);
        $associatedProduct->expects($this->never())->method('addAssociatedProduct');
        $associatedProduct->expects($this->never())->method('addAssociatedProductModel');
        $this->expectException('\LogicException');
        $this->sut->createInversedAssociation($owner, 'xsell', $associatedProduct);
    }

    public function test_it_removes_the_product_from_the_inversed_association(): void
    {
        $associatedProduct = $this->createMock(ProductInterface::class);

        $owner = new Product();
        $associatedProduct->expects($this->once())->method('removeAssociatedProduct')->with($owner, 'xsell');
        $this->entityManager->expects($this->once())->method('persist')->with($associatedProduct);
        $this->sut->removeInversedAssociation($owner, 'xsell', $associatedProduct);
    }

    public function test_it_removes_a_product(): void
    {
        $associatedProduct = $this->createMock(ProductInterface::class);

        $owner = new Product();
        $associatedProduct->method('hasAssociationForTypeCode')->with('xsell')->willReturn(true);
        $associatedProduct->expects($this->once())->method('removeAssociatedProduct')->with($owner, 'xsell');
        $this->entityManager->expects($this->once())->method('persist')->with($associatedProduct);
        $this->sut->removeInversedAssociation($owner, 'xsell', $associatedProduct);
    }

    public function test_it_removes_the_product_model_from_the_inversed_association(): void
    {
        $associatedProduct = $this->createMock(ProductInterface::class);

        $owner = new ProductModel();
        $associatedProduct->expects($this->once())->method('removeAssociatedProductModel')->with($owner, 'xsell');
        $this->entityManager->expects($this->once())->method('persist')->with($associatedProduct);
        $this->sut->removeInversedAssociation($owner, 'xsell', $associatedProduct);
    }

    public function test_it_removes_a_product_model(): void
    {
        $associatedProduct = $this->createMock(ProductInterface::class);

        $owner = new ProductModel();
        $associatedProduct->method('hasAssociationForTypeCode')->with('xsell')->willReturn(true);
        $associatedProduct->expects($this->once())->method('removeAssociatedProductModel')->with($owner, 'xsell');
        $this->entityManager->expects($this->once())->method('persist')->with($associatedProduct);
        $this->sut->removeInversedAssociation($owner, 'xsell', $associatedProduct);
    }

    public function test_it_removes_only_product_or_product_model(): void
    {
        $associatedProduct = $this->createMock(ProductInterface::class);
        $owner = $this->createMock(EntityWithAssociationsInterface::class);

        $associatedProduct->method('hasAssociationForTypeCode')->with('xsell')->willReturn(true);
        $associatedProduct->expects($this->never())->method('removeAssociatedProduct');
        $associatedProduct->expects($this->never())->method('removeAssociatedProductModel');
        $this->entityManager->expects($this->never())->method('persist')->with($associatedProduct);
        $this->expectException('\LogicException');
        $this->sut->removeInversedAssociation($owner, 'xsell', $associatedProduct);
    }

    public function test_it_does_not_associate_product_with_itself(): void
    {
        $ownerAndAssociateProduct = new Product();
        $ownerAndAssociateProduct->setIdentifier('owner_and_associate_product_identifier');
        $this->expectException(TwoWayAssociationWithTheSameProductException::class);
        $this->sut->createInversedAssociation($ownerAndAssociateProduct, 'xsell', $ownerAndAssociateProduct);
    }

    public function test_it_associates_a_product_without_identifier(): void
    {
        $associatedProduct = $this->createMock(ProductInterface::class);

        $owner = new Product();
        $owner->setIdentifier(null);
        $clonedOwner = new Product($owner->getUuid()->toString());
        $clonedOwner->setIdentifier(null);
        $associatedProduct->method('getIdentifier')->willReturn(null);
        $associatedProduct->method('getUuid')->willReturn(Uuid::uuid4());
        $associatedProduct->method('hasAssociationForTypeCode')->with('xsell')->willReturn(true);
        $associatedProduct->method('getAssociatedProducts')->with('xsell')->willReturn(new ArrayCollection([$clonedOwner]));
        $associatedProduct->expects($this->once())->method('removeAssociatedProduct')->with($clonedOwner, 'xsell');
        $associatedProduct->expects($this->once())->method('addAssociatedProduct')->with($owner, 'xsell');
        $this->sut->createInversedAssociation($owner, 'xsell', $associatedProduct);
    }
}
