<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Datasource;

use Akeneo\Pim\Enrichment\Component\Product\Grid\Query;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Rows;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ParameterizableInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductAndProductModelDatasource;
use Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductAndProductModelDatasourceTest extends TestCase
{
    private ObjectManager|MockObject $objectManager;
    private ProductQueryBuilderFactoryInterface|MockObject $pqbFactory;
    private NormalizerInterface|MockObject $rowNormalizer;
    private ValidatorInterface|MockObject $validator;
    private Query\FetchProductAndProductModelRows|MockObject $query;
    private ProductAndProductModelDatasource $sut;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);
        $this->pqbFactory = $this->createMock(ProductQueryBuilderFactoryInterface::class);
        $this->rowNormalizer = $this->createMock(NormalizerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->query = $this->createMock(Query\FetchProductAndProductModelRows::class);
        $this->sut = new ProductAndProductModelDatasource($this->objectManager, $this->pqbFactory, $this->rowNormalizer, $this->validator, $this->query);
        $this->sut->setParameters(['dataLocale' => 'fr_FR', 'scopeCode' => 'ecommerce']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ProductAndProductModelDatasource::class, $this->sut);
    }

    public function test_it_is_a_datasource(): void
    {
        $this->assertInstanceOf(DatasourceInterface::class, $this->sut);
        $this->assertInstanceOf(ParameterizableInterface::class, $this->sut);
    }

    public function test_it_fetches_product_and_product_model_rows(): void
    {
        $datagrid = $this->createMock(Datagrid::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);

        $violations = new ConstraintViolationList();
        $this->validator->method('validate')->with($this->isInstanceOf(Query\FetchProductAndProductModelRowsParameters::class))->willReturn($violations);
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
                    ],
                    'locale_code' => 'fr_FR',
                    'scope_code' => 'ecommerce',
                    PagerExtension::PER_PAGE_PARAM => 15,
                ];
        $this->pqbFactory->method('create')->with([
                    'repository_parameters' => [],
                    'repository_method'     => 'createQueryBuilder',
                    'limit'                 => 15,
                    'from'                  => 0,
                    'default_locale'        => 'fr_FR',
                    'default_scope'         => 'ecommerce',
                    'with_document_type_facet' => true,
                ])->willReturn($pqb);
        $pqb->expects($this->exactly(1))->method('getQueryBuilder');
        $row = Row::fromProduct(
            'identifier',
            'family label',
            ['group_1', 'group_2'],
            true,
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTime('2018-05-23 15:55:50', new \DateTimeZone('UTC')),
            'label',
            null,
            90,
            '54162e35-ff81-48f1-96d5-5febd3f00fd5',
            'parent_code',
            new WriteValueCollection()
        );
        $this->query->method('__invoke')->with(new Query\FetchProductAndProductModelRowsParameters(
            $pqb,
            ['attribute_1', 'attribute_2'],
            'ecommerce',
            'fr_FR'
        ))->willReturn(new Rows([$row], 1, 1, 0));
        $this->sut->process($datagrid, $config);
        $this->rowNormalizer->method('normalize')->with($row, 'datagrid', [
                    'locales'       => ['fr_FR'],
                    'channels'      => ['ecommerce'],
                    'data_locale'   => 'fr_FR',
                    'data_channel' => 'ecommerce',
                ])->willReturn([
                    'identifier'   => 'identifier',
                    'family'       => 'family label',
                    'groups'       => 'group_1,group_2',
                    'enabled'      => true,
                    'values'       => [],
                    'created'      => '2018-05-23T15:55:50+01:00',
                    'updated'      => '2018-05-23T15:55:50+01:00',
                    'label'        => 'data',
                    'image'        => null,
                    'completeness' => 90,
                    'document_type' => 'product',
                    'technical_id' => 1,
                    'id'           => 1,
                    'search_id' => 'product_1',
                    'is_checked' => true,
                    'complete_variant_product' => [],
                    'parent' => 'parent_code',
                ]);
        $results = $this->getResults();
        $results->shouldBeArray();
        $results->shouldHaveCount(4);
        $results->shouldHaveKey('data');
        $results->shouldHaveKeyWithValue('totalRecords', 1);
        $results->shouldHaveKeyWithValue('totalProducts', 1);
        $results->shouldHaveKeyWithValue('totalProductModels', 0);
        $results['data']->shouldBeArray();
        $results['data']->shouldHaveCount(1);
        $results['data']->shouldBeAnArrayOfInstanceOf(ResultRecord::class);
    }

    public function test_it_does_not_fetch_rows_when_query_parameters_are_invalid(): void
    {
        $constraint = $this->createMock(ConstraintViolation::class);
        $datagrid = $this->createMock(Datagrid::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);

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
                    ],
                    'locale_code' => 'fr_FR',
                    'scope_code' => 'ecommerce',
                    PagerExtension::PER_PAGE_PARAM => 15,
                ];
        $this->pqbFactory->method('create')->with([
                    'repository_parameters' => [],
                    'repository_method'     => 'createQueryBuilder',
                    'limit'                 => 15,
                    'from'                  => 0,
                    'default_locale'        => 'fr_FR',
                    'default_scope'         => 'ecommerce',
                    'with_document_type_facet' => true,
                ])->willReturn($pqb);
        $pqb->expects($this->exactly(1))->method('getQueryBuilder');
        $this->sut->process($datagrid, $config);
        $violations = new ConstraintViolationList([$constraint]);
        $constraint->method('__toString')->willReturn('error');
        $this->validator->method('validate')->with($this->isInstanceOf(Query\FetchProductAndProductModelRowsParameters::class))->willReturn($violations);
        $this->sut->shouldThrow(
            \LogicException::class
        )->during(
            'getResults',
            []
        );
    }

    // TODO: Custom matchers from getMatchers() need manual conversion
}
