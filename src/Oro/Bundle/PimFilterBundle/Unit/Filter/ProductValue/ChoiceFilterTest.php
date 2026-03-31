<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimFilterBundle\Filter\ProductValue;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter as OroChoiceFilter;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Oro\Bundle\PimFilterBundle\Filter\ProductValue\ChoiceFilter;

/**
 * Testable interface that adds the Doctrine magic method findOneByCode.
 */
interface TestableAttributeRepositoryInterface extends AttributeRepositoryInterface
{
    public function findOneByCode(string $code): ?object;
}
use Oro\Bundle\PimFilterBundle\Form\Type\Filter\AjaxChoiceFilterType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;

class ChoiceFilterTest extends TestCase
{
    private FormFactoryInterface|MockObject $factory;
    private ProductFilterUtility|MockObject $utility;
    private UserContext|MockObject $userContext;
    private AttributeRepositoryInterface|MockObject $attributeRepository;
    private AttributeOptionRepositoryInterface|MockObject $attributeOptionRepository;
    private ChoiceFilter $sut;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactoryInterface::class);
        $this->utility = $this->createMock(ProductFilterUtility::class);
        $this->userContext = $this->createMock(UserContext::class);
        $this->attributeRepository = $this->createMock(TestableAttributeRepositoryInterface::class);
        $this->attributeOptionRepository = $this->createMock(AttributeOptionRepositoryInterface::class);
        $this->sut = new ChoiceFilter($this->factory, $this->utility, $this->userContext, $this->attributeRepository, $this->attributeOptionRepository);
        $this->sut->init(
            'foo',
            [
        ProductFilterUtility::DATA_NAME_KEY => 'data_name_key',
        ]
        );
    }

    public function test_it_is_an_oro_choice_filter(): void
    {
        $this->assertInstanceOf(OroChoiceFilter::class, $this->sut);
    }

    public function test_it_initializes_filter_with_name(): void
    {
        $this->assertSame('foo', $this->sut->getName());
    }

    public function test_it_applies_choice_filter_on_datasource_for_array_value(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $this->attributeRepository->method('findOneByCode')->with('data_name_key')->willReturn($attribute);
        $this->attributeOptionRepository->method('findCodesByIdentifiers')->with($this->anything(), ['foo', 'bar'])->willReturn([['code' => 'foo'], ['code' => 'bar']]);
        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'data_name_key', 'IN', ['foo', 'bar']);
        $this->assertSame(true, $this->sut->apply(
            $datasource,
            [
                        'value' => ['foo', 'bar'],
                        'type'  => AjaxChoiceFilterType::TYPE_CONTAINS,
                    ]
        ));
    }

    public function test_it_applies_choice_filter_on_datasource_for_collection_value(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);
        $collection = $this->createMock(Collection::class);

        $this->attributeRepository->method('findOneByCode')->with('data_name_key')->willReturn($attribute);
        $collection->method('count')->willReturn(2);
        $collection->method('getValues')->willReturn(['foo', 'bar']);
        $this->attributeOptionRepository->method('findCodesByIdentifiers')->with($this->anything(), ['foo', 'bar'])->willReturn([['code' => 'foo'], ['code' => 'bar']]);
        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'data_name_key', 'IN', ['foo', 'bar']);
        $this->sut->apply(
            $datasource,
            [
                        'value' => $collection,
                        'type'  => AjaxChoiceFilterType::TYPE_CONTAINS,
                    ]
        );
    }

    public function test_it_applies_choice_filter_on_datasource_for_array_value_with_not_contains_type(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $this->attributeRepository->method('findOneByCode')->with('data_name_key')->willReturn($attribute);
        $this->attributeOptionRepository->method('findCodesByIdentifiers')->with($this->anything(), ['foo', 'bar'])->willReturn([['code' => 'foo'], ['code' => 'bar']]);
        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'data_name_key', 'NOT IN', ['foo', 'bar']);
        $this->sut->apply(
            $datasource,
            [
                        'value' => ['foo', 'bar'],
                        'type'  => AjaxChoiceFilterType::TYPE_NOT_CONTAINS,
                    ]
        );
    }

    public function test_it_falbacks_on_contains_type_if_type_is_unknown(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $this->attributeRepository->method('findOneByCode')->with('data_name_key')->willReturn($attribute);
        $this->attributeOptionRepository->method('findCodesByIdentifiers')->with($this->anything(), ['foo', 'bar'])->willReturn([['code' => 'foo'], ['code' => 'bar']]);
        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'data_name_key', 'IN', ['foo', 'bar']);
        $this->sut->apply(
            $datasource,
            [
                        'value' => ['foo', 'bar'],
                        'type'  => 'unknown',
                    ]
        );
    }

    public function test_it_provides_a_choice_filter_form(): void
    {
        $form = $this->createMock(Form::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $this->attributeRepository->method('findOneByCode')->with('data_name_key')->willReturn($attribute);
        $this->attributeOptionRepository->method('getClassName')->willReturn('attributeOptionClass');
        $this->userContext->method('getCurrentLocaleCode')->willReturn('en_US');
        $this->factory->method('create')->with(AjaxChoiceFilterType::class, [], [
                    'csrf_protection'   => false,
                    'choice_url'        => 'pim_ui_ajaxentity_list',
                    'choice_url_params' => [
                        'class'        => 'attributeOptionClass',
                        'dataLocale'   => 'en_US',
                        'collectionId' => null,
                        'options'      => [
                            'type' => 'code',
                        ],
                    ],
                ])->willReturn($form);
        $this->assertSame($form, $this->sut->getForm());
    }
}
