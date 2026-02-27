<?php

namespace Akeneo\Tool\Bundle\ElasticsearchBundle;

/**
 * Object that represents refresh parameter to control when changes made by this request are made visible to search.
 *
 * @author    Arnaud Langlade <arnaud.langlade@@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * {@link https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-refresh.html}
 */
final class Refresh
{
    public const ENABLE = true;
    public const DISABLE = false;
    public const WAIT_FOR = 'wait_for';

    /**
     * @param string $type
     */
    private function __construct(private $type) {}

    /**
     * @return Refresh
     */
    public static function enable()
    {
        return new self(Refresh::ENABLE);
    }

    /**
     * @return Refresh
     */
    public static function disabled()
    {
        @trigger_error('The ' . __FUNCTION__ . ' function is deprecated and will be removed in a future version.', E_USER_DEPRECATED);

        return self::disable();
    }

    /**
     * @return Refresh
     */
    public static function disable()
    {
        return new self(Refresh::DISABLE);
    }

    /**
     * @return Refresh
     */
    public static function waitFor()
    {
        return new self(Refresh::WAIT_FOR);
    }

    public function getType(): bool|string
    {
        return $this->type;
    }
}
