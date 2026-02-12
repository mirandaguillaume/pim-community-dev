<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Model;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiDebugLogLevels
{
    final public const NOTICE = 'notice';
    final public const INFO = 'info';
    final public const WARNING = 'warning';
    final public const ERROR = 'error';

    final public const ALL = [
        self::INFO,
        self::NOTICE,
        self::WARNING,
        self::ERROR,
    ];
}
