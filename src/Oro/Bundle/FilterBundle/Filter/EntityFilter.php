<?php

namespace Oro\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\EntityFilterType;

class EntityFilter extends ChoiceFilter
{
    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function init($name, array $params)
    {
        $params[FilterUtility::FRONTEND_TYPE_KEY] = 'choice';
        parent::init($name, $params);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function getFormType()
    {
        return EntityFilterType::class;
    }
}
