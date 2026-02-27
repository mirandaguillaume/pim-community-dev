<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\DTO;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppDeletion
{
    public function __construct(
        private readonly string $appId,
        private readonly string $connectionCode,
        private readonly string $userGroupName,
        private readonly string $userRole
    ) {
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getConnectionCode(): string
    {
        return $this->connectionCode;
    }

    public function getUserGroupName(): string
    {
        return $this->userGroupName;
    }

    public function getUserRole(): string
    {
        return $this->userRole;
    }
}
