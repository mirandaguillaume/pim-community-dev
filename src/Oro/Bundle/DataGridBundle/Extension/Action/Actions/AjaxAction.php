<?php

namespace Oro\Bundle\DataGridBundle\Extension\Action\Actions;

class AjaxAction extends AbstractAction
{
    /**
     * @var array
     */
    protected $requiredOptions = [];

    /**
     * @return array
     */
    #[\Override]
    public function getOptions()
    {
        $options = parent::getOptions();

        $options['frontend_type'] = 'ajax';

        return $options;
    }
}
