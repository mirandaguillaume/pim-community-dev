<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Datasource;

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
use Oro\Bundle\PimDataGridBundle\Datasource\AssociatedProductDatasource;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ParameterizableInterface;
use Oro\Bundle\PimDataGridBundle\EventSubscriber\FilterEntityWithValuesSubscriber;
use Oro\Bundle\PimDataGridBundle\Normalizer\IdEncoder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssociatedProductDatasourceTest extends TestCase
{
    private ObjectManager|MockObject $objectManager;
    private ProductQueryBuilderFactoryInterface|MockObject $pqbFactory;
    private NormalizerInterface|MockObject $productNormalizer;
    private FilterEntityWithValuesSubscriber|MockObject $subscriber;
    private NormalizerInterface|MockObject $internalApiNormalizer;
    private AssociatedProductDatasource $sut;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->pqbFactory = $this->createMock(ProductQueryBuilderFactoryInterface::class);
        $this->productNormalizer = $this->createMock(NormalizerInterface::class);
        $this->subscriber = $this->createMock(FilterEntityWithValuesSubscriber::class);
        $this->internalApiNormalizer = $this->createMock(NormalizerInterface::class);
        $this->sut = new AssociatedProductDatasource($this->objectManager, $this->pqbFactory, $this->productNormalizer, $this->subscriber, $this->internalApiNormalizer);
        $this->sut->setSortOrder(Directions::DESCENDING);
        $this->sut->setParameters(['dataLocale' => 'a_locale']);
        $this->sut->setParameters(['dataChannel' => 'a_channel']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AssociatedProductDatasource::class, $this->sut);
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
        $this->expectException(InvalidObjectException::class);
        $this->sut->getResults();
    }

    public function test_it_returns_empty_when_no_association(): void
    {
        $datagrid = $this->createMock(Datagrid::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);
        $currentProduct = $this->createMock(ProductInterface::class);
        $associationCollection = $this->createMock(Collection::class);
        $associationIterator = $this->createMock(\ArrayIterator::class);

        $this->pqbFactory->method('create')->willReturn($pqb);
        $pqb->method('getQueryBuilder');
        $this->sut->process($datagrid, [
            'locale_code'         => 'a_locale',
            'scope_code'          => 'a_channel',
            '_per_page'           => 42,
            'current_product'     => $currentProduct,
            'association_type_id' => '999',
        ]);
        $currentProduct->method('getAllAssociations')->willReturn($associationCollection);
        $associationCollection->method('getIterator')->willReturn($associationIterator);
        $associationIterator->method('rewind');
        $associationIterator->method('valid')->willReturn(false);

        $results = $this->sut->getResults();
        $this->assertSame(0, $results['totalRecords']);
        $this->assertSame([], $results['data']);
    }

    public function test_it_gets_products_and_product_models_sorted_by_association_status(): void
    {
        $datagrid = $this->createMock(Datagrid::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);
        $pqbAsso = $this->createMock(ProductQueryBuilderInterface::class);
        $pqbAssoProductModel = $this->createMock(ProductQueryBuilderInterface::class);
        $currentProduct = $this->createMock(ProductInterface::class);
        $parent = $this->createMock(ProductModelInterface::class);
        $associatedProduct1 = $this->createMock(ProductInterface::class);
        $associatedProduct2 = $this->createMock(ProductInterface::class);
        $associatedProductModel = $this->createMock(ProductModelInterface::class);
        $associationCollection = $this->createMock(Collection::class);
        $parentAssociationCollection = $this->createMock(Collection::class);
        $association = $this->createMock(AssociationInterface::class);
        $parentAssociation = $this->createMock(AssociationInterface::class);
        $associationType = $this->createMock(AssociationTypeInterface::class);
        $associationIterator = $this->createMock(\ArrayIterator::class);
        $parentAssociationIterator = $this->createMock(\ArrayIterator::class);
        $productCursor = $this->createMock(CursorInterface::class);
        $associatedProductCursor = $this->createMock(CursorInterface::class);
        $associatedProductModelCursor = $this->createMock(CursorInterface::class);
        $collectionProductModel = $this->createMock(Collection::class);
        $parentCollectionProductModel = $this->createMock(Collection::class);
        $collectionProductModelIterator = $this->createMock(\Iterator::class);
        $parentCollectionProductModelIterator = $this->createMock(\Iterator::class);

        $uuid1 = '57700274-9b48-4857-b17d-a7da106cd150';
        $uuid2 = '0cc93a87-0b93-4246-939a-9d9d7a84302d';

        $this->pqbFactory->method('create')->willReturnCallback(
            function (array $options) use ($pqb, $pqbAsso, $pqbAssoProductModel) {
                if (!array_key_exists('filters', $options)) {
                    return $pqb;
                }
                if ($options['limit'] === 42) {
                    return $pqbAsso;
                }
                return $pqbAssoProductModel;
            }
        );
        $pqb->expects($this->exactly(1))->method('getQueryBuilder');
        $this->sut->process($datagrid, [
                    'locale_code'         => 'a_locale',
                    'scope_code'          => 'a_channel',
                    '_per_page'           => 42,
                    'current_product'     => $currentProduct,
                    'association_type_id' => '1',
                ]);
        $associatedProduct1->method('getUuid')->willReturn(Uuid::fromString($uuid1));
        $associatedProduct2->method('getUuid')->willReturn(Uuid::fromString($uuid2));
        $associatedProductModel->method('getId')->willReturn(1);
        $currentProduct->method('getAllAssociations')->willReturn($associationCollection);
        $currentProduct->method('getIdentifier')->willReturn('current_product');
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

        // Verify exact filters and sorter applied to pqbAsso (product query)
        $pqbAssoFilterCalls = [];
        $pqbAsso->method('addFilter')->willReturnCallback(
            function (string $field, string $operator, $value) use (&$pqbAssoFilterCalls) {
                $pqbAssoFilterCalls[] = [$field, $operator, $value];
            }
        );
        $pqbAsso->expects($this->once())->method('addSorter')->with('id', Directions::ASCENDING);
        $pqbAsso->method('execute')->willReturn($associatedProductCursor);
        $associatedProductCursor->expects($this->once())->method('rewind');
        $associatedProductCursor->method('valid')->willReturn(true, true, false);
        $associatedProductCursor->method('current')->willReturn($associatedProduct1, $associatedProduct2);
        $associatedProductCursor->expects($this->exactly(2))->method('next');
        $associatedProductCursor->method('count')->willReturn(2);

        // Verify exact filters and sorter applied to pqbAssoProductModel
        $pqbPmFilterCalls = [];
        $pqbAssoProductModel->method('addFilter')->willReturnCallback(
            function (string $field, string $operator, $value) use (&$pqbPmFilterCalls) {
                $pqbPmFilterCalls[] = [$field, $operator, $value];
            }
        );
        $pqbAssoProductModel->expects($this->once())->method('addSorter')->with('id', Directions::ASCENDING);
        $pqbAssoProductModel->method('execute')->willReturn($associatedProductModelCursor);
        $associatedProductModelCursor->expects($this->once())->method('rewind');
        $associatedProductModelCursor->method('valid')->willReturn(true, false);
        $associatedProductModelCursor->method('current')->willReturn($associatedProductModel);
        $associatedProductModelCursor->expects($this->once())->method('next');
        $associatedProductModelCursor->method('count')->willReturn(1);

        $this->productNormalizer->method('normalize')->willReturnCallback(
            function ($object, $format, $ctx) use ($associatedProduct1, $associatedProduct2, $associatedProductModel) {
                $this->assertSame('datagrid', $format);
                // Verify normalization context
                $this->assertSame(['a_locale'], $ctx['locales']);
                $this->assertSame(['a_channel'], $ctx['channels']);
                $this->assertSame('a_locale', $ctx['data_locale']);
                $this->assertSame('a_channel', $ctx['data_channel']);
                $this->assertTrue($ctx['is_associated']);

                if ($object === $associatedProduct1) {
                    return [
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
                    ];
                }
                if ($object === $associatedProduct2) {
                    return [
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
                    ];
                }
                if ($object === $associatedProductModel) {
                    return [
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
                    ];
                }
                return null;
            }
        );
        $productSourceNormalized = ['identifier' => 'current_product'];
        $this->internalApiNormalizer->expects($this->once())->method('normalize')
            ->with($currentProduct, 'internal_api', [
                'locales'     => ['a_locale'],
                'channels'    => ['a_channel'],
                'data_locale' => 'a_locale',
            ])
            ->willReturn($productSourceNormalized);

        $results = $this->sut->getResults();

        $this->assertIsArray($results);
        $this->assertCount(3, $results);
        $this->assertArrayHasKey('data', $results);
        $this->assertArrayHasKey('totalRecords', $results);
        $this->assertArrayHasKey('meta', $results);
        $this->assertSame(3, $results['totalRecords']);
        $this->assertSame($productSourceNormalized, $results['meta']['source']);

        // Verify data records
        $this->assertCount(3, $results['data']);
        $this->assertInstanceOf(ResultRecord::class, $results['data'][0]);
        $this->assertInstanceOf(ResultRecord::class, $results['data'][1]);
        $this->assertInstanceOf(ResultRecord::class, $results['data'][2]);

        // Verify product 1 normalized data (product type)
        $record0 = $results['data'][0];
        $this->assertSame('product-' . $uuid1, $record0->getValue('id'));
        $this->assertSame('a_locale', $record0->getValue('dataLocale'));
        $this->assertTrue($record0->getValue('is_associated'));
        $this->assertFalse($record0->getValue('from_inheritance'));

        // Verify product 2 normalized data (product type, from parent)
        $record1 = $results['data'][1];
        $this->assertSame('product-' . $uuid2, $record1->getValue('id'));
        $this->assertSame('a_locale', $record1->getValue('dataLocale'));
        $this->assertTrue($record1->getValue('is_associated'));
        $this->assertTrue($record1->getValue('from_inheritance'));

        // Verify product model normalized data
        $record2 = $results['data'][2];
        $this->assertSame('product-model-1', $record2->getValue('id'));
        $this->assertSame('a_locale', $record2->getValue('dataLocale'));
        $this->assertTrue($record2->getValue('is_associated'));
        $this->assertFalse($record2->getValue('from_inheritance'));

        // Verify PQB filter calls for products
        $this->assertContains(['id', Operators::IN_LIST, [
            IdEncoder::encode(IdEncoder::PRODUCT_TYPE, $uuid1),
            IdEncoder::encode(IdEncoder::PRODUCT_TYPE, $uuid2),
        ]], $pqbAssoFilterCalls);
        $this->assertContains(['entity_type', Operators::EQUALS, ProductInterface::class], $pqbAssoFilterCalls);

        // Verify PQB filter calls for product models
        $this->assertContains(['id', Operators::IN_LIST, [
            IdEncoder::encode(IdEncoder::PRODUCT_MODEL_TYPE, 1),
        ]], $pqbPmFilterCalls);
        $this->assertContains(['entity_type', Operators::EQUALS, ProductModelInterface::class], $pqbPmFilterCalls);
    }

    public function test_it_gets_only_products_because_of_limit_reached(): void
    {
        $datagrid = $this->createMock(Datagrid::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);
        $pqbAsso = $this->createMock(ProductQueryBuilderInterface::class);
        $currentProduct = $this->createMock(ProductInterface::class);
        $parent = $this->createMock(ProductModelInterface::class);
        $associatedProduct1 = $this->createMock(ProductInterface::class);
        $associatedProduct2 = $this->createMock(ProductInterface::class);
        $associatedProductModel = $this->createMock(ProductModelInterface::class);
        $associationCollection = $this->createMock(Collection::class);
        $parentAssociationCollection = $this->createMock(Collection::class);
        $association = $this->createMock(AssociationInterface::class);
        $parentAssociation = $this->createMock(AssociationInterface::class);
        $associationType = $this->createMock(AssociationTypeInterface::class);
        $associationIterator = $this->createMock(\ArrayIterator::class);
        $parentAssociationIterator = $this->createMock(\ArrayIterator::class);
        $productCursor = $this->createMock(CursorInterface::class);
        $associatedProductCursor = $this->createMock(CursorInterface::class);
        $collectionProductModel = $this->createMock(Collection::class);
        $parentCollectionProductModel = $this->createMock(Collection::class);
        $collectionProductModelIterator = $this->createMock(\Iterator::class);
        $parentCollectionProductModelIterator = $this->createMock(\Iterator::class);

        $uuid1 = '57700274-9b48-4857-b17d-a7da106cd150';
        $uuid2 = '0cc93a87-0b93-4246-939a-9d9d7a84302d';

        $this->pqbFactory->method('create')->willReturnCallback(
            function (array $options) use ($pqb, $pqbAsso) {
                if (!array_key_exists('filters', $options)) {
                    return $pqb;
                }
                return $pqbAsso;
            }
        );
        $pqb->expects($this->exactly(1))->method('getQueryBuilder');
        $this->sut->process($datagrid, [
                    'locale_code'         => 'a_locale',
                    'scope_code'          => 'a_channel',
                    '_per_page'           => 2,
                    'current_product'     => $currentProduct,
                    'association_type_id' => '1',
                ]);
        $associatedProduct1->method('getUuid')->willReturn(Uuid::fromString($uuid1));
        $associatedProduct2->method('getUuid')->willReturn(Uuid::fromString($uuid2));
        $associatedProductModel->method('getId')->willReturn(1);
        $currentProduct->method('getAllAssociations')->willReturn($associationCollection);
        $currentProduct->method('getIdentifier')->willReturn('current_product');
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
        $pqb->expects($this->exactly(1))->method('getRawFilters')->willReturn(null);
        $pqbAsso->expects($this->atLeastOnce())->method('addFilter');
        $pqbAsso->expects($this->once())->method('addSorter')->with('id', Directions::ASCENDING);
        $pqbAsso->method('execute')->willReturn($associatedProductCursor);
        $associatedProductCursor->expects($this->once())->method('rewind');
        $associatedProductCursor->method('valid')->willReturn(true, true, false);
        $associatedProductCursor->method('current')->willReturn($associatedProduct1, $associatedProduct2);
        $associatedProductCursor->expects($this->exactly(2))->method('next');
        $associatedProductCursor->method('count')->willReturn(2);
        $this->productNormalizer->method('normalize')->willReturnCallback(
            function ($object) use ($associatedProduct1, $associatedProduct2) {
                if ($object === $associatedProduct1) {
                    return [
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
                    ];
                }
                if ($object === $associatedProduct2) {
                    return [
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
                    ];
                }
                return null;
            }
        );
        $results = $this->sut->getResults();
        $this->assertIsArray($results);
        $this->assertCount(3, $results);
        $this->assertArrayHasKey('data', $results);
        $this->assertSame(2, $results['totalRecords']);

        // Verify only 2 products (no product models because limit was reached)
        $this->assertCount(2, $results['data']);

        // Verify product 1 record content
        $record0 = $results['data'][0];
        $this->assertInstanceOf(ResultRecord::class, $record0);
        $this->assertSame('product-' . $uuid1, $record0->getValue('id'));
        $this->assertSame('a_locale', $record0->getValue('dataLocale'));
        $this->assertTrue($record0->getValue('is_associated'));
        // product1 was NOT in parent's association
        $this->assertFalse($record0->getValue('from_inheritance'));

        // Verify product 2 record content
        $record1 = $results['data'][1];
        $this->assertInstanceOf(ResultRecord::class, $record1);
        $this->assertSame('product-' . $uuid2, $record1->getValue('id'));
        $this->assertTrue($record1->getValue('is_associated'));
        // product2 was in parent's association
        $this->assertTrue($record1->getValue('from_inheritance'));
    }

    public function test_it_gets_results_with_no_parent(): void
    {
        $datagrid = $this->createMock(Datagrid::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);
        $pqbAsso = $this->createMock(ProductQueryBuilderInterface::class);
        $pqbAssoPm = $this->createMock(ProductQueryBuilderInterface::class);
        $currentProduct = $this->createMock(ProductInterface::class);
        $associatedProduct1 = $this->createMock(ProductInterface::class);
        $associationCollection = $this->createMock(Collection::class);
        $association = $this->createMock(AssociationInterface::class);
        $associationType = $this->createMock(AssociationTypeInterface::class);
        $associationIterator = $this->createMock(\ArrayIterator::class);
        $productCursor = $this->createMock(CursorInterface::class);
        $associatedProductCursor = $this->createMock(CursorInterface::class);
        $associatedPmCursor = $this->createMock(CursorInterface::class);

        $uuid1 = '57700274-9b48-4857-b17d-a7da106cd150';

        $callIdx = 0;
        $this->pqbFactory->method('create')->willReturnCallback(
            function (array $options) use ($pqb, $pqbAsso, $pqbAssoPm, &$callIdx) {
                $callIdx++;
                if (!array_key_exists('filters', $options)) {
                    return $pqb;
                }
                if ($callIdx === 2) {
                    return $pqbAsso;
                }
                return $pqbAssoPm;
            }
        );
        $pqb->method('getQueryBuilder');
        $this->sut->process($datagrid, [
            'locale_code'         => 'a_locale',
            'scope_code'          => 'a_channel',
            '_per_page'           => 42,
            'current_product'     => $currentProduct,
            'association_type_id' => '1',
        ]);
        $associatedProduct1->method('getUuid')->willReturn(Uuid::fromString($uuid1));
        $currentProduct->method('getAllAssociations')->willReturn($associationCollection);
        $currentProduct->method('getParent')->willReturn(null);
        $associationCollection->method('getIterator')->willReturn($associationIterator);
        $associationIterator->method('rewind');
        $associationIterator->method('valid')->willReturn(true, false);
        $associationIterator->method('current')->willReturn($association);
        $association->method('getProducts')->willReturn([$associatedProduct1]);
        $emptyPmCollection = $this->createMock(Collection::class);
        $emptyPmIterator = $this->createMock(\Iterator::class);
        $emptyPmCollection->method('getIterator')->willReturn($emptyPmIterator);
        $emptyPmIterator->method('rewind');
        $emptyPmIterator->method('valid')->willReturn(false);
        $association->method('getProductModels')->willReturn($emptyPmCollection);
        $association->method('getAssociationType')->willReturn($associationType);
        $associationType->method('getId')->willReturn(1);
        $pqb->method('execute')->willReturn($productCursor);
        $productCursor->method('count')->willReturn(1);
        $pqb->method('getRawFilters')->willReturn(null);
        $pqbAsso->method('addFilter');
        $pqbAsso->method('addSorter');
        $pqbAsso->method('execute')->willReturn($associatedProductCursor);
        $associatedProductCursor->method('rewind');
        $associatedProductCursor->method('valid')->willReturn(true, false);
        $associatedProductCursor->method('current')->willReturn($associatedProduct1);
        $associatedProductCursor->method('next');
        $associatedProductCursor->method('count')->willReturn(1);

        // Empty PM cursor for the product model PQB
        $pqbAssoPm->method('addFilter');
        $pqbAssoPm->method('addSorter');
        $pqbAssoPm->method('execute')->willReturn($associatedPmCursor);
        $associatedPmCursor->method('rewind');
        $associatedPmCursor->method('valid')->willReturn(false);
        $associatedPmCursor->method('count')->willReturn(0);

        $this->productNormalizer->method('normalize')->willReturn([
            'identifier' => 'product_1',
        ]);
        $this->internalApiNormalizer->method('normalize')->willReturn(['identifier' => 'current']);

        $results = $this->sut->getResults();
        $this->assertSame(1, $results['totalRecords']);
        $this->assertCount(1, $results['data']);
        $record = $results['data'][0];
        // No parent means from_inheritance should be false
        $this->assertFalse($record->getValue('from_inheritance'));
        // With no parent, parentAssociatedProductsIds is empty so no from_inheritance
        $this->assertSame('product-' . $uuid1, $record->getValue('id'));
    }

    public function test_createQueryBuilder_uses_repository_parameters_and_method(): void
    {
        $datagrid = $this->createMock(Datagrid::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);
        $pqbAsso = $this->createMock(ProductQueryBuilderInterface::class);
        $pqbAssoPm = $this->createMock(ProductQueryBuilderInterface::class);
        $currentProduct = $this->createMock(ProductInterface::class);
        $associationCollection = $this->createMock(Collection::class);
        $association = $this->createMock(AssociationInterface::class);
        $associationType = $this->createMock(AssociationTypeInterface::class);
        $associationIterator = $this->createMock(\ArrayIterator::class);
        $productCursor = $this->createMock(CursorInterface::class);
        $associatedProductCursor = $this->createMock(CursorInterface::class);
        $associatedPmCursor = $this->createMock(CursorInterface::class);
        $associatedProduct1 = $this->createMock(ProductInterface::class);

        $uuid1 = '57700274-9b48-4857-b17d-a7da106cd150';

        $capturedConfigs = [];
        $callIdx = 0;
        $this->pqbFactory->method('create')->willReturnCallback(
            function (array $options) use ($pqb, $pqbAsso, $pqbAssoPm, &$capturedConfigs, &$callIdx) {
                $capturedConfigs[] = $options;
                $callIdx++;
                if (!array_key_exists('filters', $options)) {
                    return $pqb;
                }
                if ($callIdx === 2) {
                    return $pqbAsso;
                }
                return $pqbAssoPm;
            }
        );
        $pqb->method('getQueryBuilder');
        $this->sut->process($datagrid, [
            'locale_code'           => 'en_US',
            'scope_code'            => 'ecommerce',
            '_per_page'             => 10,
            'current_product'       => $currentProduct,
            'association_type_id'   => '1',
            'repository_parameters' => ['param1' => 'val1'],
            'repository_method'     => 'customMethod',
        ]);
        $associatedProduct1->method('getUuid')->willReturn(Uuid::fromString($uuid1));
        $currentProduct->method('getAllAssociations')->willReturn($associationCollection);
        $currentProduct->method('getParent')->willReturn(null);
        $associationCollection->method('getIterator')->willReturn($associationIterator);
        $associationIterator->method('rewind');
        $associationIterator->method('valid')->willReturn(true, false);
        $associationIterator->method('current')->willReturn($association);
        $association->method('getProducts')->willReturn([$associatedProduct1]);
        $emptyPmCollection2 = $this->createMock(Collection::class);
        $emptyPmIterator2 = $this->createMock(\Iterator::class);
        $emptyPmCollection2->method('getIterator')->willReturn($emptyPmIterator2);
        $emptyPmIterator2->method('rewind');
        $emptyPmIterator2->method('valid')->willReturn(false);
        $association->method('getProductModels')->willReturn($emptyPmCollection2);
        $association->method('getAssociationType')->willReturn($associationType);
        $associationType->method('getId')->willReturn(1);
        $pqb->method('execute')->willReturn($productCursor);
        $productCursor->method('count')->willReturn(1);
        $pqb->method('getRawFilters')->willReturn(null);
        $pqbAsso->method('addFilter');
        $pqbAsso->method('addSorter');
        $pqbAsso->method('execute')->willReturn($associatedProductCursor);
        $associatedProductCursor->method('rewind');
        $associatedProductCursor->method('valid')->willReturn(true, false);
        $associatedProductCursor->method('current')->willReturn($associatedProduct1);
        $associatedProductCursor->method('next');
        $associatedProductCursor->method('count')->willReturn(1);

        // Empty PM cursor
        $pqbAssoPm->method('addFilter');
        $pqbAssoPm->method('addSorter');
        $pqbAssoPm->method('execute')->willReturn($associatedPmCursor);
        $associatedPmCursor->method('rewind');
        $associatedPmCursor->method('valid')->willReturn(false);
        $associatedPmCursor->method('count')->willReturn(0);

        $this->productNormalizer->method('normalize')->willReturn(['identifier' => 'p1']);
        $this->internalApiNormalizer->method('normalize')->willReturn(['identifier' => 'current']);

        $this->sut->getResults();

        // The second factory create call (for associated products) should pass repo config
        $this->assertGreaterThanOrEqual(2, count($capturedConfigs));
        $assocPqbConfig = $capturedConfigs[1];
        $this->assertSame(['param1' => 'val1'], $assocPqbConfig['repository_parameters']);
        $this->assertSame('customMethod', $assocPqbConfig['repository_method']);
        $this->assertSame('en_US', $assocPqbConfig['default_locale']);
        $this->assertSame('ecommerce', $assocPqbConfig['default_scope']);
        $this->assertSame(10, $assocPqbConfig['limit']);
        $this->assertSame(0, $assocPqbConfig['from']);
    }
}
