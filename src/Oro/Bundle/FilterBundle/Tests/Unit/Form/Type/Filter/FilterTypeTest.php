<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Tests\Unit\Fixtures\CustomFormExtension;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FilterTypeTest extends AbstractTypeTestCase
{
    /**
     * @var FilterType
     */
    protected $type;

    protected function setUp(): void
    {
        $translator = $this->createMockTranslator();
        $this->type = new FilterType($translator);
        $this->formExtensions[] = new CustomFormExtension([$this->type]);

        parent::setUp();
    }

    public function testFieldAndOperatorAreNotRequired(): void
    {
        $form = $this->factory->create(FilterType::class);

        $this->assertFalse($form->get('value')->getConfig()->getOption('required'));
        $this->assertFalse($form->get('type')->getConfig()->getOption('required'));
    }

    public function testOperatorOptionsAreMerged(): void
    {
        $form = $this->factory->create(FilterType::class, null, [
            'operator_choices' => [1 => 'Choice 1'],
            'operator_options' => ['attr' => ['class' => 'custom']],
        ]);

        $this->assertSame(['class' => 'custom'], $form->get('type')->getConfig()->getOption('attr'));
    }

    public function testFieldOptionsAreMerged(): void
    {
        $form = $this->factory->create(FilterType::class, null, [
            'field_options' => ['attr' => ['class' => 'field-class']],
        ]);

        $this->assertSame(['class' => 'field-class'], $form->get('value')->getConfig()->getOption('attr'));
    }

    public function testEmptyChoiceAddsEmptyAndNotEmptyToOperator(): void
    {
        $form = $this->factory->create(FilterType::class, null, [
            'field_options' => ['attr' => ['empty_choice' => true]],
            'operator_choices' => [1 => 'Choice 1'],
        ]);

        $typeChoices = $form->get('type')->getConfig()->getOption('choices');
        $this->assertArrayHasKey('oro.filter.form.label_type_empty', $typeChoices);
        $this->assertArrayHasKey('oro.filter.form.label_type_not_empty', $typeChoices);
    }

    /**
     * {@inheritDoc}
     */
    protected function getTestFormType()
    {
        return $this->type;
    }

    public function testGetName()
    {
        $this->assertEquals(FilterType::NAME, $this->type->getBlockPrefix());
    }

    /**
     * {@inheritDoc}
     */
    public static function configureOptionsDataProvider()
    {
        return [
            [
                'defaultOptions' => [
                    'field_type'       => TextType::class,
                    'field_options'    => [],
                    'operator_choices' => [],
                    'operator_type'    => ChoiceType::class,
                    'operator_options' => [],
                    'show_filter'      => false,
                ],
                'requiredOptions' => [
                    'field_type',
                    'field_options',
                    'operator_choices',
                    'operator_type',
                    'operator_options',
                    'show_filter',
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function bindDataProvider()
    {
        return [
            'empty data' => [
                'bindData' => [],
                'formData' => ['type' => null, 'value' => null],
                'viewData' => [
                    'value' => ['type' => '', 'value' => ''],
                ],
                'customOptions' => [
                    'operator_choices' => [],
                ],
            ],
            'empty choice' => [
                'bindData' => ['type'  => '1', 'value' => ''],
                'formData' => ['value' => null],
                'viewData' => [
                    'value' => ['type' => '1', 'value' => ''],
                ],
                'customOptions' => [
                    'operator_choices' => [],
                ],
            ],
            'invalid choice' => [
                'bindData' => ['type'  => '-1', 'value' => ''],
                'formData' => ['value' => null],
                'viewData' => [
                    'value' => ['type' => '-1', 'value' => ''],
                ],
                'customOptions' => [
                    'operator_choices' => [
                        1 => 'Choice 1',
                    ],
                ],
            ],
            'without choice' => [
                'bindData' => ['value' => 'text'],
                'formData' => ['type'  => null, 'value' => 'text'],
                'viewData' => [
                    'value' => ['type' => '', 'value' => 'text'],
                ],
                'customOptions' => [
                    'operator_choices' => [
                        1 => 'Choice 1',
                    ],
                ],
            ],
        ];
    }
}
