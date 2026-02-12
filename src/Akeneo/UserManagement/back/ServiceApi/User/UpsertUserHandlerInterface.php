<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\UserManagement\ServiceApi\User;

interface UpsertUserHandlerInterface
{
    public const TYPE_USER = 'user';
    public const TYPE_API = 'api';
    public const TYPE_JOB = 'job';

    public function handle(UpsertUserCommand $upsertUserCommand): void;
}
