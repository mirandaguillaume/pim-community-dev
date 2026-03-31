<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
use Oro\Bundle\FilterBundle\Tests\Unit\Fixtures\CustomFormExtension;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TextFilterTypeTest extends AbstractTypeTestCase
{
    private \Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType $type;

    protected function setUp(): void
    {
        $translator = $this->createMockTranslator();
        $this->type = new TextFilterType($translator);
        $this->formExtensions[] = new CustomFormExtension([new FilterType($translator), $this->type]);

        parent::setUp();
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
        $this->assertEquals(TextFilterType::NAME, $this->type->getBlockPrefix());
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
                    'operator_choices' => [
                        TextFilterType::TYPE_CONTAINS     => 'oro.filter.form.label_type_contains',
                        TextFilterType::TYPE_NOT_CONTAINS => 'oro.filter.form.label_type_not_contains',
                        TextFilterType::TYPE_EQUAL        => 'oro.filter.form.label_type_equals',
                        TextFilterType::TYPE_STARTS_WITH  => 'oro.filter.form.label_type_start_with',
                        TextFilterType::TYPE_EMPTY        => 'oro.filter.form.label_type_empty',
                    ],
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
            'simple text' => [
                'bindData' => ['type' => TextFilterType::TYPE_CONTAINS, 'value' => 'text'],
                'formData' => ['type' => TextFilterType::TYPE_CONTAINS, 'value' => 'text'],
                'viewData' => [
                    'value' => ['type' => TextFilterType::TYPE_CONTAINS, 'value' => 'text'],
                ],
            ],
        ];
    }
}
