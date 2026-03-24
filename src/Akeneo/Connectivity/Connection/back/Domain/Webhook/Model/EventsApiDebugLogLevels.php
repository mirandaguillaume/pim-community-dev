<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Webhook\Model;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EventsApiDebugLogLevels
{
    final public const string NOTICE = 'notice';
    final public const string INFO = 'info';
    final public const string WARNING = 'warning';
    final public const string ERROR = 'error';

    final public const array ALL = [
        self::INFO,
        self::NOTICE,
        self::WARNING,
        self::ERROR,
    ];
}
