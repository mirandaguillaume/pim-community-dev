<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Model;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class EventTypes
{
    public const string PRODUCT_CREATED = 'product_created';
    public const string PRODUCT_UPDATED = 'product_updated';
    public const string PRODUCT_READ = 'product_read';
}
