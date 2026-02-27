<?php

namespace Oro\Bundle\FilterBundle\Form\Type\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class TextFilterType extends AbstractType
{
    final public const TYPE_CONTAINS = 1;
    final public const TYPE_NOT_CONTAINS = 2;
    final public const TYPE_EQUAL = 3;
    final public const TYPE_STARTS_WITH = 4;
    final public const TYPE_ENDS_WITH = 5;
    final public const TYPE_EMPTY = 'empty';
    final public const NAME = 'oro_type_text_filter';

    public function __construct(protected TranslatorInterface $translator) {}

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): ?string
    {
        return FilterType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $choices = [
            self::TYPE_CONTAINS     => $this->translator->trans('oro.filter.form.label_type_contains'),
            self::TYPE_NOT_CONTAINS => $this->translator->trans('oro.filter.form.label_type_not_contains'),
            self::TYPE_EQUAL        => $this->translator->trans('oro.filter.form.label_type_equals'),
            self::TYPE_STARTS_WITH  => $this->translator->trans('oro.filter.form.label_type_start_with'),
            self::TYPE_EMPTY        => $this->translator->trans('oro.filter.form.label_type_empty'),
        ];

        $resolver->setDefaults(
            [
                'field_type'       => TextType::class,
                'operator_choices' => $choices,
            ]
        );
    }
}
