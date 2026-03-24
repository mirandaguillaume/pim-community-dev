<?php

namespace Oro\Bundle\SecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ObjectLabelType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_acl_label';
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getParent(): ?string
    {
        return HiddenType::class;
    }
}
