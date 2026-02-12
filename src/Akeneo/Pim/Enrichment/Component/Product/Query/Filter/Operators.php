<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query\Filter;

/**
 * Filter operators
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Operators
{
    final public const STARTS_WITH = 'STARTS WITH';
    final public const ENDS_WITH = 'ENDS WITH';
    final public const CONTAINS = 'CONTAINS';
    final public const DOES_NOT_CONTAIN = 'DOES NOT CONTAIN';
    final public const IS_EMPTY = 'EMPTY';
    final public const IS_NOT_EMPTY = 'NOT EMPTY';
    final public const IN_LIST = 'IN';
    final public const NOT_IN_LIST = 'NOT IN';
    final public const IN_CHILDREN_LIST = 'IN CHILDREN';
    final public const NOT_IN_CHILDREN_LIST = 'NOT IN CHILDREN';
    final public const UNCLASSIFIED = 'UNCLASSIFIED';
    final public const IN_LIST_OR_UNCLASSIFIED = 'IN OR UNCLASSIFIED';
    final public const IN_ARRAY_KEYS = 'IN ARRAY KEYS';
    final public const BETWEEN = 'BETWEEN';
    final public const NOT_BETWEEN = 'NOT BETWEEN';
    final public const IS_NULL = 'NULL';
    final public const IS_NOT_NULL = 'NOT NULL';
    final public const IS_LIKE = 'LIKE';
    final public const IS_NOT_LIKE = 'NOT LIKE';
    final public const GREATER_THAN = '>';
    final public const GREATER_OR_EQUAL_THAN = '>=';
    final public const LOWER_THAN = '<';
    final public const LOWER_OR_EQUAL_THAN = '<=';
    final public const EQUALS = '=';
    final public const NOT_EQUAL = '!=';
    final public const SINCE_LAST_N_DAYS = 'SINCE LAST N DAYS';
    final public const SINCE_LAST_JOB = 'SINCE LAST JOB';
    final public const NOT_EQUALS_ON_AT_LEAST_ONE_LOCALE = 'NOT EQUALS ON AT LEAST ONE LOCALE';
    final public const EQUALS_ON_AT_LEAST_ONE_LOCALE = 'EQUALS ON AT LEAST ONE LOCALE';
    final public const GREATER_THAN_ON_AT_LEAST_ONE_LOCALE = 'GREATER THAN ON AT LEAST ONE LOCALE';
    final public const GREATER_OR_EQUALS_THAN_ON_AT_LEAST_ONE_LOCALE = 'GREATER OR EQUALS THAN ON AT LEAST ONE LOCALE';
    final public const LOWER_OR_EQUALS_THAN_ON_AT_LEAST_ONE_LOCALE = 'LOWER OR EQUALS THAN ON AT LEAST ONE LOCALE';
    final public const LOWER_THAN_ON_AT_LEAST_ONE_LOCALE = 'LOWER THAN ON AT LEAST ONE LOCALE';
    final public const GREATER_THAN_ON_ALL_LOCALES = 'GREATER THAN ON ALL LOCALES';
    final public const GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES = 'GREATER OR EQUALS THAN ON ALL LOCALES';
    final public const LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES = 'LOWER OR EQUALS THAN ON ALL LOCALES';
    final public const LOWER_THAN_ON_ALL_LOCALES = 'LOWER THAN ON ALL LOCALES';
    final public const IS_EMPTY_FOR_CURRENCY = 'EMPTY FOR CURRENCY';
    final public const IS_EMPTY_ON_ALL_CURRENCIES = 'EMPTY ON ALL CURRENCIES';
    final public const IS_NOT_EMPTY_FOR_CURRENCY = 'NOT EMPTY FOR CURRENCY';
    final public const IS_NOT_EMPTY_ON_AT_LEAST_ONE_CURRENCY = 'NOT EMPTY ON AT LEAST ONE CURRENCY';
    final public const AT_LEAST_COMPLETE = 'AT LEAST COMPLETE';
    final public const AT_LEAST_INCOMPLETE = 'AT LEAST INCOMPLETE';
    final public const ALL_COMPLETE = 'ALL COMPLETE';
    final public const ALL_INCOMPLETE = 'ALL INCOMPLETE';
}
