<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Oro\Bundle\PimFilterBundle\Form\Type\DateTimeRangeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeRangeFilterType extends AbstractType
{
    final public const int TYPE_BETWEEN = DateRangeFilterType::TYPE_BETWEEN;
    final public const int TYPE_NOT_BETWEEN = DateRangeFilterType::TYPE_NOT_BETWEEN;
    final public const string NAME = 'oro_type_datetime_range_filter';

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getParent(): ?string
    {
        return DateRangeFilterType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'field_type' => DateTimeRangeType::class,
            ]
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $widgetOptions = ['firstDay' => 0];
        $view->vars['widget_options'] = array_merge($widgetOptions, $options['widget_options']);
    }
}
