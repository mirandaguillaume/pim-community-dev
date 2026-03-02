<?php

namespace Oro\Bundle\PimFilterBundle\Form\Type\Filter;

use Akeneo\UserManagement\Bundle\Context\UserContext;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Overriding of ChoiceFilterType
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopeFilterType extends ChoiceFilterType
{
    /** @staticvar string */
    public const NAME = 'pim_type_scope_filter';

    public function __construct(TranslatorInterface $translator, protected UserContext $userContext)
    {
        parent::__construct($translator);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return ChoiceFilterType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $scopeChoices = $this->userContext->getChannelChoicesWithUserChannel();

        $resolver->setDefaults(
            [
                'field_type'    => ChoiceType::class,
                'field_options' => ['choices' => $scopeChoices],
            ]
        );
    }
}
