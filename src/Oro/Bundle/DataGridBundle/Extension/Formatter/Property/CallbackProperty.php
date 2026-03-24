<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class CallbackProperty extends AbstractProperty
{
    final public const string CALLABLE_KEY = 'callable';

    /** @var array */
    protected $excludeParams = [self::CALLABLE_KEY];

    /**
     * {@inheritdoc}
     */
    public function getRawValue(ResultRecordInterface $record): mixed
    {
        return call_user_func($this->get(self::CALLABLE_KEY), $record);
    }
}
