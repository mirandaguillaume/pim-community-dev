<?php

namespace Oro\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;

class FilterUtility
{
    final public const CONDITION_OR = 'OR';
    final public const CONDITION_AND = 'AND';

    final public const CONDITION_KEY = 'filter_condition';
    final public const BY_HAVING_KEY = 'filter_by_having';
    final public const ENABLED_KEY = 'enabled';
    final public const TYPE_KEY = 'type';
    final public const FRONTEND_TYPE_KEY = 'ftype';
    final public const DATA_NAME_KEY = 'data_name';
    final public const FORM_OPTIONS_KEY = 'options';

    public function getParamMap()
    {
        return [self::FRONTEND_TYPE_KEY => self::TYPE_KEY];
    }

    public function getExcludeParams()
    {
        return [self::DATA_NAME_KEY, self::FORM_OPTIONS_KEY];
    }

    /**
     * Applies filter to query by field
     */
    public function applyFilter(FilterDatasourceAdapterInterface $ds, string $field, string $operator, mixed $value): never
    {
        throw new \RuntimeException('Not implemented. Use your own FilterUtility to implement this method.');
    }
}
