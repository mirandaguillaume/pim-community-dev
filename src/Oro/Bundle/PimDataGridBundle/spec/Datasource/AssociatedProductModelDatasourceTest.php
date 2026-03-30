<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Datasource;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\PimDataGridBundle\Datasource\AssociatedProductModelDatasource;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ParameterizableInterface;
use Oro\Bundle\PimDataGridBundle\EventSubscriber\FilterEntityWithValuesSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssociatedProductModelDatasourceTest extends TestCase
{
    private ObjectManager|MockObject $objectManager;
    private ProductQueryBuilderFactoryInterface|MockObject $pqbFactory;
    private NormalizerInterface|MockObject $productNormalizer;
    private FilterEntityWithValuesSubscriber|MockObject $subscriber;
    private NormalizerInterface|MockObject $internalApiNormalizer;
    private AssociatedProductModelDatasource $sut;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->pqbFactory = $this->createMock(ProductQueryBuilderFactoryInterface::class);
        $this->productNormalizer = $this->createMock(NormalizerInterface::class);
        $this->subscriber = $this->createMock(FilterEntityWithValuesSubscriber::class);
        $this->internalApiNormalizer = $this->createMock(NormalizerInterface::class);
        $this->sut = new AssociatedProductModelDatasource($this->objectManager, $this->pqbFactory, $this->productNormalizer, $this->subscriber, $this->internalApiNormalizer);
        $this->sut->setSortOrder(Directions::DESCENDING);
        $this->sut->setParameters(['dataLocale' => 'a_locale']);
        $this->sut->setParameters(['dataChannel' => 'a_channel']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AssociatedProductModelDatasource::class, $this->sut);
    }

    public function test_it_is_a_datasource(): void
    {
        $this->assertInstanceOf(DatasourceInterface::class, $this->sut);
        $this->assertInstanceOf(ParameterizableInterface::class, $this->sut);
    }

    public function test_it_throws_an_exception_when_there_is_no_current_product(): void
    {
        $datagrid = $this->createMock(Datagrid::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);

        $this->pqbFactory->method('create')->with($this->anything())->willReturn($pqb);
        $pqb->expects($this->once())->method('getQueryBuilder');
        $this->sut->process($datagrid, [
                    'locale_code'     => 'a_locale',
                    'scope_code'      => 'a_channel',
                    '_per_page'       => 42,
                    'current_product' => 'not a product instance',
                ]);
        $this->sut->shouldThrow(
            InvalidObjectException::objectExpected(
                'not a product instance',
                ProductModelInterface::class
            )
        )->during('getResults');
    }

    public function test_it_gets_product_models_sorted_by_association_status(): void
    {
        $datagrid = $this->createMock(Datagrid::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);
        $pqbAsso = $this->createMock(ProductQueryBuilderInterface::class);
        $pqbAssoProductModel = $this->createMock(ProductQueryBuilderInterface::class);
        $currentProduct = $this->createMock(ProductModelInterface::class);
        $parent = $this->createMock(ProductModelInterface::class);
        $associatedProduct1 = $this->createMock(ProductInterface::class);
        $associatedProduct2 = $this->createMock(ProductInterface::class);
        $associatedProductModel = $this->createMock(ProductModelInterface::class);
        $associationCollection = $this->createMock(Collection::class);
        $parentAssociationCollection = $this->createMock(Collection::class);
        $association = $this->createMock(AssociationInterface::class);
        $parentAssociation = $this->createMock(AssociationInterface::class);
        $associationType = $this->createMock(AssociationTypeInterface::class);
        $associationIterator = $this->createMock(ArrayIterator::class);
        $parentAssociationIterator = $this->createMock(ArrayIterator::class);
        $productCursor = $this->createMock(CursorInterface::class);
        $associatedProductCursor = $this->createMock(CursorInterface::class);
        $associatedProductModelCursor = $this->createMock(CursorInterface::class);
        $collectionProductModel = $this->createMock(Collection::class);
        $parentCollectionProductModel = $this->createMock(Collection::class);
        $collectionProductModelIterator = $this->createMock(Iterator::class);
        $parentCollectionProductModelIterator = $this->createMock(Iterator::class);

        $this->pqbFactory->method('create')->with([
                    'repository_parameters' => [],
                    'repository_method'     => 'createQueryBuilder',
                    'limit'                 => 42,
                    'from'                  => 0,
                    'default_locale'        => 'a_locale',
                    'default_scope'         => 'a_channel',
                ])->willReturn($pqb);
        $pqb->expects($this->exactly(1))->method('getQueryBuilder');
        $this->sut->process($datagrid, [
                    'locale_code'         => 'a_locale',
                    'scope_code'          => 'a_channel',
                    '_per_page'           => 42,
                    'current_product'     => $currentProduct,
                    'association_type_id' => '1',
                ]);
        $associatedProduct1Uuid = '57700274-9b48-4857-b17d-a7da106cd150';
        $associatedProduct1->method('getIdentifier')->willReturn('associated_product_1');
        $associatedProduct1->method('getUuid')->willReturn(Uuid::fromString($associatedProduct1Uuid));
        $associatedProduct2Uuid = '0cc93a87-0b93-4246-939a-9d9d7a84302d';
        $associatedProduct2->method('getIdentifier')->willReturn('associated_product_2');
        $associatedProduct2->method('getUuid')->willReturn(Uuid::fromString($associatedProduct2Uuid));
        $associatedProductModel->method('getCode')->willReturn('associated_product_model_1');
        $associatedProductModel->method('getId')->willReturn('2');
        $currentProduct->method('getAllAssociations')->willReturn($associationCollection);
        $currentProduct->method('getParent')->willReturn($parent);
        $parent->method('getAllAssociations')->willReturn($parentAssociationCollection);
        $parentAssociationCollection->method('getIterator')->willReturn($parentAssociationIterator);
        $parentAssociationIterator->expects($this->once())->method('rewind');
        $parentAssociationIterator->method('valid')->willReturn(true, false);
        $parentAssociationIterator->method('current')->willReturn($parentAssociation);
        $associationCollection->method('getIterator')->willReturn($associationIterator);
        $associationIterator->expects($this->once())->method('rewind');
        $associationIterator->method('valid')->willReturn(true, false);
        $associationIterator->method('current')->willReturn($association);
        $association->method('getProducts')->willReturn([$associatedProduct1, $associatedProduct2]);
        $parentAssociation->method('getProducts')->willReturn([$associatedProduct2]);
        $association->method('getProductModels')->willReturn($collectionProductModel);
        $parentAssociation->method('getProductModels')->willReturn($parentCollectionProductModel);
        $collectionProductModel->method('getIterator')->willReturn($collectionProductModelIterator);
        $collectionProductModelIterator->expects($this->once())->method('rewind');
        $collectionProductModelIterator->method('valid')->willReturn(true, false);
        $collectionProductModelIterator->method('current')->willReturn($associatedProductModel);
        $collectionProductModelIterator->expects($this->once())->method('next');
        $parentCollectionProductModel->method('getIterator')->willReturn($parentCollectionProductModelIterator);
        $parentCollectionProductModelIterator->expects($this->once())->method('rewind');
        $parentCollectionProductModelIterator->method('valid')->willReturn(false);
        $association->method('getAssociationType')->willReturn($associationType);
        $parentAssociation->method('getAssociationType')->willReturn($associationType);
        $associationType->method('getId')->willReturn(1);
        $pqb->method('execute')->willReturn($productCursor);
        $productCursor->method('count')->willReturn(2);
        $pqb->expects($this->exactly(2))->method('getRawFilters')->willReturn(null);
        $this->pqbFactory->method('create')->with([
                    'repository_parameters' => [],
                    'repository_method'     => 'createQueryBuilder',
                    'limit'                 => 42,
                    'from'                  => 0,
                    'default_locale'        => 'a_locale',
                    'default_scope'         => 'a_channel',
                    'filters'               => null,
                ])->willReturn($pqbAsso);
        $pqbAsso->expects($this->once())->method('addFilter')->with(
            'id',
            Operators::IN_LIST,
            ["product_{$associatedProduct1Uuid}", "product_{$associatedProduct2Uuid}"]
        );
        $pqbAsso->expects($this->once())->method('addFilter')->with(
            'entity_type',
            Operators::EQUALS,
            ProductInterface::class
        );
        $pqbAsso->method('execute')->willReturn($associatedProductCursor);
        $associatedProductCursor->expects($this->once())->method('rewind');
        $associatedProductCursor->method('valid')->willReturn(true, true, false);
        $associatedProductCursor->method('current')->willReturn($associatedProduct1, $associatedProduct2);
        $associatedProductCursor->expects($this->once())->method('next');
        $associatedProductCursor->method('count')->willReturn(2);
        $this->pqbFactory->method('create')->with([
                    'repository_parameters' => [],
                    'repository_method'     => 'createQueryBuilder',
                    'limit'                 => 40,
                    'from'                  => 0,
                    'default_locale'        => 'a_locale',
                    'default_scope'         => 'a_channel',
                    'filters'               => null,
                ])->willReturn($pqbAssoProductModel);
        $pqbAssoProductModel->expects($this->once())->method('addFilter')->with(
            'identifier',
            Operators::IN_LIST,
            ['associated_product_model_1']
        );
        $pqbAssoProductModel->expects($this->once())->method('addFilter')->with(
            'entity_type',
            Operators::EQUALS,
            ProductModelInterface::class
        );
        $pqbAssoProductModel->method('execute')->willReturn($associatedProductModelCursor);
        $associatedProductModelCursor->expects($this->once())->method('rewind');
        $associatedProductModelCursor->method('valid')->willReturn(true, false);
        $associatedProductModelCursor->method('current')->willReturn($associatedProductModel);
        $associatedProductModelCursor->expects($this->once())->method('next');
        $associatedProductModelCursor->method('count')->willReturn(1);
        $this->productNormalizer->expects($this->never())->method('normalize');
        $this->productNormalizer->method('normalize')->with($associatedProduct1, 'datagrid', [
                    'locales'       => ['a_locale'],
                    'channels'      => ['a_channel'],
                    'data_locale'   => 'a_locale',
                    'data_channel'  => 'a_channel',
                    'is_associated' => true,
                ])->willReturn([
                    'identifier'    => 'associated_product_1',
                    'family'        => null,
                    'enabled'       => true,
                    'values'        => [],
                    'created'       => '2000-01-01',
                    'updated'       => '2000-01-01',
                    'is_checked'    => true,
                    'is_associated' => true,
                    'label'         => 'associated_product_1',
                    'completeness'  => null,
                    'from_inheritance' => false,
                ]);
        $this->productNormalizer->method('normalize')->with($associatedProduct2, 'datagrid', [
                    'locales'       => ['a_locale'],
                    'channels'      => ['a_channel'],
                    'data_locale'   => 'a_locale',
                    'data_channel'  => 'a_channel',
                    'is_associated' => true,
                ])->willReturn([
                    'identifier'    => 'associated_product_2',
                    'family'        => null,
                    'enabled'       => true,
                    'values'        => [],
                    'created'       => '2000-01-01',
                    'updated'       => '2000-01-01',
                    'is_checked'    => true,
                    'is_associated' => true,
                    'label'         => 'associated_product_2',
                    'completeness'  => null,
                    'from_inheritance' => true,
                ]);
        $this->productNormalizer->method('normalize')->with($associatedProductModel, 'datagrid', [
                    'locales'       => ['a_locale'],
                    'channels'      => ['a_channel'],
                    'data_locale'   => 'a_locale',
                    'data_channel'  => 'a_channel',
                    'is_associated' => true,
                ])->willReturn([
                    'identifier'    => 'associated_product_model_1',
                    'family'        => null,
                    'enabled'       => true,
                    'values'        => [],
                    'created'       => '2000-01-01',
                    'updated'       => '2000-01-01',
                    'is_checked'    => true,
                    'is_associated' => true,
                    'label'         => 'associated_product_model_1',
                    'completeness'  => null,
                ]);
        $productSourceNormalized = [
                    'identifier' => 'current_product',
                ];
        $this->internalApiNormalizer->method('normalize')->willReturn($productSourceNormalized);
        $results = $this->getResults();
        $results->shouldBeArray();
        $results->shouldHaveCount(3);
        $results->shouldHaveKey('data');
        $results->shouldHaveKeyWithValue('totalRecords', 3);
        $results['data']->shouldBeArray();
        $results['data']->shouldHaveCount(3);
        $results['data']->shouldBeAnArrayOfInstanceOf(ResultRecord::class);
        $results['data'][0]->getValue('id')->shouldReturn('product-57700274-9b48-4857-b17d-a7da106cd150');
        $results['data'][1]->getValue('id')->shouldReturn('product-0cc93a87-0b93-4246-939a-9d9d7a84302d');
        $results['data'][2]->getValue('id')->shouldReturn('product-model-2');
        $results['meta']->shouldBe([
                    'source' => $productSourceNormalized,
                ]);
    }

    // TODO: Custom matchers from getMatchers() need manual conversion
}
