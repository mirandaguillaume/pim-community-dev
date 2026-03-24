<?php

namespace Oro\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;

class FilterUtility
{
    final public const string CONDITION_OR = 'OR';
    final public const string CONDITION_AND = 'AND';

    final public const string CONDITION_KEY = 'filter_condition';
    final public const string BY_HAVING_KEY = 'filter_by_having';
    final public const string ENABLED_KEY = 'enabled';
    final public const string TYPE_KEY = 'type';
    final public const string FRONTEND_TYPE_KEY = 'ftype';
    final public const string DATA_NAME_KEY = 'data_name';
    final public const string FORM_OPTIONS_KEY = 'options';

    public function getParamMap(): array
    {
        return [self::FRONTEND_TYPE_KEY => self::TYPE_KEY];
    }

    public function getExcludeParams(): array
    {
        return [self::DATA_NAME_KEY, self::FORM_OPTIONS_KEY];
    }

    /**
     * Applies filter to query by field
     */
    public function applyFilter(FilterDatasourceAdapterInterface $ds, string $field, string $operator, mixed $value): void
    {
        throw new \RuntimeException('Not implemented. Use your own FilterUtility to implement this method.');
    }
}
