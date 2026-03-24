<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\OptionsResolver\OptionsResolver;

class BooleanFilterType extends AbstractChoiceType
{
    final public const int TYPE_YES = 1;
    final public const int TYPE_NO = 2;
    final public const string NAME = 'oro_type_boolean_filter';

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
        return ChoiceFilterType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $fieldChoices = [
            $this->translator->trans('oro.filter.form.label_type_yes') => self::TYPE_YES,
            $this->translator->trans('oro.filter.form.label_type_no') => self::TYPE_NO,
        ];

        $resolver->setDefaults(
            [
                'field_options' => ['choices' => $fieldChoices],
            ]
        );
    }
}
