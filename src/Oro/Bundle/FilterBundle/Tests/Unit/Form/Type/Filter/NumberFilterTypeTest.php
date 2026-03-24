<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\FilterBundle\Tests\Unit\Fixtures\CustomFormExtension;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;

class NumberFilterTypeTest extends AbstractTypeTestCase
{
    private \Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType $type;

    protected ?string $defaultLocale = 'en';

    protected function setUp(): void
    {
        $translator = $this->createMockTranslator();
        $this->formExtensions[] = new CustomFormExtension([new FilterType($translator)]);

        parent::setUp();
        $this->type = new NumberFilterType($translator);
    }

    /**
     * {@inheritDoc}
     */
    protected function getTestFormType(): \Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType
    {
        return $this->type;
    }

    public function testGetName(): void
    {
        $this->assertEquals(NumberFilterType::NAME, $this->type->getBlockPrefix());
    }

    /**
     * {@inheritDoc}
     */
    public static function configureOptionsDataProvider(): array
    {
        return [
            [
                'defaultOptions' => [
                    'field_type'       => 'number',
                    'operator_choices' => [
                        NumberFilterType::TYPE_EQUAL         => 'oro.filter.form.label_type_equal',
                        NumberFilterType::TYPE_GREATER_EQUAL => 'oro.filter.form.label_type_greater_equal',
                        NumberFilterType::TYPE_GREATER_THAN  => 'oro.filter.form.label_type_greater_than',
                        NumberFilterType::TYPE_LESS_EQUAL    => 'oro.filter.form.label_type_less_equal',
                        NumberFilterType::TYPE_LESS_THAN     => 'oro.filter.form.label_type_less_than',
                    ],
                    'data_type'         => NumberFilterType::DATA_INTEGER,
                    'formatter_options' => [],
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function bindDataProvider(): array
    {
        return [
            'not formatted number' => [
                'bindData' => ['type' => NumberFilterType::TYPE_EQUAL, 'value' => '12345.67890'],
                'formData' => ['type' => NumberFilterType::TYPE_EQUAL, 'value' => 12345.6789],
                'viewData' => [
                    'value' => ['type' => NumberFilterType::TYPE_EQUAL, 'value' => '12,345.68'],
                ],
                'customOptions' => [
                    'field_options' => ['grouping' => true, 'precision' => 2],
                ],
            ],
            'formatted number' => [
                'bindData' => ['type' => NumberFilterType::TYPE_EQUAL, 'value' => '12,345.68'],
                'formData' => ['type' => NumberFilterType::TYPE_EQUAL, 'value' => 12345.68],
                'viewData' => [
                    'value' => ['type' => NumberFilterType::TYPE_EQUAL, 'value' => '12,345.68'],
                ],
                'customOptions' => [
                    'field_options' => ['grouping' => true, 'precision' => 2],
                ],
            ],
            'integer' => [
                'bindData' => ['type' => NumberFilterType::TYPE_EQUAL, 'value' => '12345.67890'],
                'formData' => ['type' => NumberFilterType::TYPE_EQUAL, 'value' => 12345],
                'viewData' => [
                    'value'             => ['type' => NumberFilterType::TYPE_EQUAL, 'value' => '12345'],
                    'formatter_options' => [
                        'decimals'         => 0,
                        'grouping'         => false,
                        'orderSeparator'   => '',
                        'decimalSeparator' => '.',
                    ],
                ],
                'customOptions' => [
                    'field_type' => 'integer',
                    'data_type'  => NumberFilterType::DATA_INTEGER,
                ],
            ],
            'money' => [
                'bindData' => ['type' => NumberFilterType::TYPE_EQUAL, 'value' => '12345.67890'],
                'formData' => [
                    'type'  => NumberFilterType::TYPE_EQUAL,
                    'value' => 12345.6789,
                ],
                'viewData' => [
                    'value'             => ['type' => NumberFilterType::TYPE_EQUAL, 'value' => '12345.68'],
                    'formatter_options' => [
                        'decimals'         => 4,
                        'grouping'         => true,
                        'orderSeparator'   => ' ',
                        'decimalSeparator' => '.',
                    ],
                ],
                'customOptions' => [
                    'field_type'        => 'money',
                    'data_type'         => NumberFilterType::DATA_DECIMAL,
                    'formatter_options' => [
                        'decimals'       => 4,
                        'orderSeparator' => ' ',
                    ],
                ],
            ],
            'invalid format' => [
                'bindData' => ['type' => NumberFilterType::TYPE_EQUAL, 'value' => 'abcd.67890'],
                'formData' => ['type' => NumberFilterType::TYPE_EQUAL],
                'viewData' => [
                    'value' => ['type' => NumberFilterType::TYPE_EQUAL, 'value' => 'abcd.67890'],
                ],
                'customOptions' => [
                    'field_type' => 'money',
                ],
            ],
        ];
    }
}
