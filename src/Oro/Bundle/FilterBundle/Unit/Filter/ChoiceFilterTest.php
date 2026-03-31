<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\FilterBundle\Filter\FilterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\ChoiceList\View\ChoiceGroupView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class ChoiceFilterTest extends TestCase
{
    private FormFactoryInterface|MockObject $factory;
    private FilterUtility|MockObject $util;
    private ChoiceFilter $sut;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactoryInterface::class);
        $this->util = $this->createMock(FilterUtility::class);
        $this->sut = new ChoiceFilter($this->factory, $this->util);
    }

    public function test_it_is_a_filter(): void
    {
        $this->assertInstanceOf(FilterInterface::class, $this->sut);
    }

    public function test_it_gives_metadata(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $form = $this->createMock(FormInterface::class);

        $builder->method('get')->with('type')->willReturn($builder);
        $builder->method('getOption')->with('choices')->willReturn(['foo', 'bar']);
        $this->factory->method('createBuilder')->with(ChoiceFilterType::class, [], ['csrf_protection' => false])->willReturn($builder);
        $this->factory->method('create')->with(ChoiceFilterType::class, [], ['csrf_protection' => false])->willReturn($form);
        $this->util->method('getExcludeParams')->willReturn([]);
        $this->util->method('getParamMap')->willReturn([]);
        $view = new FormView();
        $fieldView = new FormView();
        $typeView = new FormView();
        $view->children['value'] = $fieldView;
        $view->children['type'] = $typeView;
        $view->vars['populate_default'] = false;
        $typeView->vars['choices'] = [];
        $fieldView->vars['multiple'] = true;
        $fieldView->vars['choices'] = [
                    new ChoiceView('name', 'name', 'Name'),
                    new ChoiceView('description', 'description', 'Description'),
                    new ChoiceGroupView('Marketing', [
                        ['label' => 'Price', 'value' => 'price'],
                    ]),
                ];
        $form->method('createView')->willReturn($view);
        $this->sut->init('choices', []);
        $this->assertSame([
                    'name' => 'choices',
                    'label' => 'Choices',
                    'choices' => [
                        ['label' => 'Name', 'value' => 'name'],
                        ['label' => 'Description', 'value' => 'description'],
                        ['label' => 'Marketing', 'value' => [
                            ['label' => 'Price', 'value' => 'price'],
                        ]],
                    ],
                    'enabled' => true,
                    'populateDefault' => false,
                    'type' => 'multichoice',
                ], $this->sut->getMetadata());
    }
}
