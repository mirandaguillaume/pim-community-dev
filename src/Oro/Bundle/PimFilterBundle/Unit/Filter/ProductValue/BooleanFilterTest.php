<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimFilterBundle\Filter\ProductValue;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Oro\Bundle\PimFilterBundle\Form\Type\Filter\BooleanFilterType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class BooleanFilterTest extends TestCase
{
    private FormFactoryInterface|MockObject $factory;
    private ProductFilterUtility|MockObject $utility;
    private BooleanFilter $sut;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactoryInterface::class);
        $this->utility = $this->createMock(ProductFilterUtility::class);
        $this->sut = new BooleanFilter($this->factory, $this->utility);
        $this->sut->init('foo', [ProductFilterUtility::DATA_NAME_KEY => 'bar']);
    }

    public function test_it_is_an_oro_boolean_filter(): void
    {
        $this->assertInstanceOf(BooleanFilter::class, $this->sut);
    }

    public function test_it_initializes_filter_with_name(): void
    {
        $this->assertSame('foo', $this->sut->getName());
    }

    public function test_it_parses_data(): void
    {
        $this->assertSame(['value' => 0], $this->sut->parseData(['value' => 0]));
        $this->assertSame(['value' => 1], $this->sut->parseData(['value' => 1]));
        $this->assertSame(['value' => 2], $this->sut->parseData(['value' => 2]));
        $this->assertSame(['value' => 3], $this->sut->parseData(['value' => 3]));
        $this->assertSame(false, $this->sut->parseData(['value' => true]));
        $this->assertSame(false, $this->sut->parseData(['value' => false]));
        $this->assertSame(false, $this->sut->parseData(null));
        $this->assertSame(false, $this->sut->parseData([]));
        $this->assertSame(false, $this->sut->parseData(1));
        $this->assertSame(false, $this->sut->parseData(0));
    }

    public function test_it_applies_boolean_flexible_filter_on_the_datasource(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'bar', '=', true);
        $this->assertSame(true, $this->sut->apply($datasource, ['value' => BooleanFilterType::TYPE_YES]));
    }

    public function test_it_applies_empty_filter_on_the_datasource(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'bar', 'EMPTY', '');
        $this->assertSame(true, $this->sut->apply($datasource, ['value' => BooleanFilterType::TYPE_EMPTY]));
    }

    public function test_it_applies_not_empty_filter_on_the_datasource(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'bar', 'NOT EMPTY', '');
        $this->assertSame(true, $this->sut->apply($datasource, ['value' => BooleanFilterType::TYPE_NOT_EMPTY]));
    }

    public function test_it_does_not_apply_boolean_flexible_filter_on_unparsable_data(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->utility->expects($this->never())->method('applyFilter');
        $this->assertSame(false, $this->sut->apply($datasource, ['value' => 'foo']));
        $this->assertSame(false, $this->sut->apply($datasource, ['value' => null]));
        $this->assertSame(false, $this->sut->apply($datasource, []));
        $this->assertSame(false, $this->sut->apply($datasource, BooleanFilterType::TYPE_NO));
    }

    public function test_it_uses_the_boolean_filter_form_type(): void
    {
        $form = $this->createMock(FormInterface::class);

        $this->factory->method('create')->with(BooleanFilterType::class, [], ['csrf_protection' => false])->willReturn($form);
        $this->assertSame($form, $this->sut->getForm());
    }

    public function test_it_generates_choices_metadata(): void
    {
        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $typeFormBuilder = $this->createMock(FormBuilderInterface::class);
        $form = $this->createMock(FormInterface::class);
        $formView = $this->createMock(FormView::class);
        $fieldView = $this->createMock(FormView::class);
        $typeView = $this->createMock(FormView::class);
        $yesChoice = $this->createMock(ChoiceView::class);
        $noChoice = $this->createMock(ChoiceView::class);
        $emptyChoice = $this->createMock(ChoiceView::class);
        $notEmptyChoice = $this->createMock(ChoiceView::class);
        $maybeChoice = $this->createMock(ChoiceView::class);

        $this->utility->method('getParamMap')->willReturn([]);
        $this->utility->method('getExcludeParams')->willReturn([]);
        $this->factory->method('create')->with(BooleanFilterType::class, [], ['csrf_protection' => false])->willReturn($form);
        $this->factory->method('createBuilder')->with(BooleanFilterType::class, [], ['csrf_protection' => false])->willReturn($formBuilder);
        $form->method('createView')->willReturn($formView);
        $formBuilder->method('get')->with('type')->willReturn($typeFormBuilder);
        $typeFormBuilder->method('getOption')->with('choices')->willReturn(['overriden_choice_1' => 0, 'overriden_choice_2' => 1]);
        $formView->children = ['value' => $fieldView, 'type' => $typeView];
        $formView->vars = ['populate_default' => true];
        $fieldView->vars = ['multiple' => true, 'choices' => [$yesChoice, $noChoice, $emptyChoice, $notEmptyChoice]];
        $typeView->vars = ['choices' => [$maybeChoice]];
        $yesChoice->label = 'Yes';
        $yesChoice->value = 1;
        $noChoice->label = 'No';
        $noChoice->value = 0;
        $emptyChoice->label = 'Empty';
        $emptyChoice->value = 2;
        $notEmptyChoice->label = 'Not empty';
        $notEmptyChoice->value = 3;
        $this->assertSame([
                        'name'                 => 'foo',
                        'label'                => 'Foo',
                        'choices'              => [
                            [
                                'label' => 'Yes',
                                'value' => 1,
                            ], [
                                'label' => 'No',
                                'value' => 0,
                            ], [
                                'label' => 'Empty',
                                'value' => 2,
                            ], [
                                'label' => 'Not empty',
                                'value' => 3,
                            ],
                        ],
                        'enabled'              => true,
                        'data_name'            => 'bar',
                        'populateDefault'      => true,
                        'type'                 => 'multichoice',
                    ], $this->sut->getMetadata());
    }
}
