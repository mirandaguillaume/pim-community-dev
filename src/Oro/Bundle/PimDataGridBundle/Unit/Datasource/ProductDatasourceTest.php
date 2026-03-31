<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Datasource;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ParameterizableInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductDatasource;
use Oro\Bundle\PimDataGridBundle\EventSubscriber\FilterEntityWithValuesSubscriber;
use Oro\Bundle\PimDataGridBundle\EventSubscriber\FilterEntityWithValuesSubscriberConfiguration;
use Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductDatasourceTest extends TestCase
{
    private ObjectManager|MockObject $objectManager;
    private ProductQueryBuilderFactoryInterface|MockObject $pqbFactory;
    private NormalizerInterface|MockObject $productNormalizer;
    private FilterEntityWithValuesSubscriber|MockObject $subscriber;
    private ProductDatasource $sut;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->pqbFactory = $this->createMock(ProductQueryBuilderFactoryInterface::class);
        $this->productNormalizer = $this->createMock(NormalizerInterface::class);
        $this->subscriber = $this->createMock(FilterEntityWithValuesSubscriber::class);
        $this->sut = new ProductDatasource($this->objectManager, $this->pqbFactory, $this->productNormalizer, $this->subscriber);
        $this->sut->setParameters(['dataLocale' => 'fr_FR']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductDatasource::class, $this->sut);
    }

    public function test_it_is_a_datasource(): void
    {
        $this->assertInstanceOf(DatasourceInterface::class, $this->sut);
        $this->assertInstanceOf(ParameterizableInterface::class, $this->sut);
    }

    public function test_it_gets_products(): void
    {
        $datagrid = $this->createMock(Datagrid::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);
        $product1 = $this->createMock(ProductInterface::class);
        $productCursor = $this->createMock(CursorInterface::class);

        $fixedUuid = Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5');
        $product1->method('getUuid')->willReturn($fixedUuid);
        $config = [
                    'displayed_attribute_ids' => [1, 2],
                    'attributes_configuration' => [
                        'attribute_1' => [
                            'id' => 1,
                            'code' => 'attribute_1',
                        ],
                        'attribute_2' => [
                            'id' => 2,
                            'code' => 'attribute_2',
                        ],
                        'attribute_3' => [
                            'id' => 3,
                            'code' => 'attribute_3',
                        ],
                        'sku' => [
                            'id' => 4,
                            'code' => 'sku',
                            'mainIdentifier' => true,
                        ],
                    ],
                    'locale_code' => 'fr_FR',
                    'scope_code' => 'ecommerce',

                    'association_type_id' => 2,
                    'current_group_id' => 3,
                    PagerExtension::PER_PAGE_PARAM => 15,
                ];
        $this->pqbFactory->method('create')->with([
                    'repository_parameters' => [],
                    'repository_method'     => 'createQueryBuilder',
                    'limit'                 => 15,
                    'from'                  => 0,
                    'default_locale'        => 'fr_FR',
                    'default_scope'         => 'ecommerce',
                ])->willReturn($pqb);
        $pqb->expects($this->exactly(1))->method('getQueryBuilder');
        $pqb->method('execute')->willReturn($productCursor);
        $productCursor->method('count')->willReturn(1);
        $productCursor->expects($this->once())->method('rewind');
        $productCursor->method('valid')->willReturn(true, false);
        $productCursor->method('current')->willReturn($product1);
        $productCursor->expects($this->once())->method('next');
        $this->sut->process($datagrid, $config);
        $this->productNormalizer->method('normalize')->with($product1, 'datagrid', [
                    'locales'       => ['fr_FR'],
                    'channels'      => ['ecommerce'],
                    'data_locale'   => 'fr_FR',
                    'association_type_id' => 2,
                    'current_group_id' => 3,
                ])->willReturn([
                    'identifier'       => 'product_1',
                    'family'           => null,
                    'enabled'          => true,
                    'label'            => 'foo',
                    'values'           => [],
                    'created'          => '2000-01-01',
                    'updated'          => '2000-01-01',
                    'compleneteness'   => null,
                    'variant_products' => null,
                    'document_type'    => null,
                ]);
        // CPM-1082: mainIdentifier attribute should be kept for display purposes in the grid
        $this->subscriber->expects($this->once())
            ->method('configure')
            ->with(FilterEntityWithValuesSubscriberConfiguration::filterEntityValues(['attribute_1', 'attribute_2', 'sku']));
        $results = $this->sut->getResults();
        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey('data', $results);
        $this->assertSame(1, $results['totalRecords']);

        // Verify the record content has correct defaults merged with normalizer output
        $this->assertCount(1, $results['data']);
        $record = $results['data'][0];
        $this->assertInstanceOf(ResultRecord::class, $record);
        $this->assertSame('54162e35-ff81-48f1-96d5-5febd3f00fd5', $record->getValue('id'));
        $this->assertSame('fr_FR', $record->getValue('dataLocale'));
        $this->assertSame('product_1', $record->getValue('identifier'));
        $this->assertTrue($record->getValue('enabled'));
        $this->assertSame('foo', $record->getValue('label'));
    }

    public function test_it_gets_products_with_from_parameter(): void
    {
        $datagrid = $this->createMock(Datagrid::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);
        $productCursor = $this->createMock(CursorInterface::class);

        $config = [
            'displayed_attribute_ids' => [],
            'attributes_configuration' => [],
            'locale_code' => 'en_US',
            'scope_code' => 'ecommerce',
            'association_type_id' => null,
            'current_group_id' => null,
            'from' => 5,
            PagerExtension::PER_PAGE_PARAM => 10,
        ];

        $this->pqbFactory->method('create')->with([
            'repository_parameters' => [],
            'repository_method'     => 'createQueryBuilder',
            'limit'                 => 10,
            'from'                  => 5,
            'default_locale'        => 'en_US',
            'default_scope'         => 'ecommerce',
        ])->willReturn($pqb);
        $pqb->method('getQueryBuilder');
        $pqb->method('execute')->willReturn($productCursor);
        $productCursor->method('count')->willReturn(0);
        $productCursor->method('rewind');
        $productCursor->method('valid')->willReturn(false);

        $this->subscriber->expects($this->once())->method('configure');

        $this->sut->process($datagrid, $config);
        $results = $this->sut->getResults();

        $this->assertSame(0, $results['totalRecords']);
        $this->assertSame([], $results['data']);
    }

    public function test_getProductQueryBuilder_returns_pqb(): void
    {
        $datagrid = $this->createMock(Datagrid::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);

        $config = [
            'displayed_attribute_ids' => [],
            'attributes_configuration' => [],
            'locale_code' => 'en_US',
            'scope_code' => 'ecommerce',
            PagerExtension::PER_PAGE_PARAM => 10,
        ];

        $this->pqbFactory->method('create')->willReturn($pqb);
        $pqb->method('getQueryBuilder');

        $this->sut->process($datagrid, $config);
        $this->assertSame($pqb, $this->sut->getProductQueryBuilder());
    }
}
