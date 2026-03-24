<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Type;

use Akeneo\Tool\Component\Localization\Provider\LocaleProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType as SymfonyLocaleType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocaleType extends AbstractType
{
    protected \Akeneo\Tool\Component\Localization\Provider\LocaleProviderInterface $localeProvider;

    public function __construct(LocaleProviderInterface $localeProvider)
    {
        $this->localeProvider = $localeProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $choices = $this->localeProvider->getLocales();
        $resolver->setDefaults(['choices' => $choices]);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getParent(): ?string
    {
        return SymfonyLocaleType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'pim_locale';
    }
}
