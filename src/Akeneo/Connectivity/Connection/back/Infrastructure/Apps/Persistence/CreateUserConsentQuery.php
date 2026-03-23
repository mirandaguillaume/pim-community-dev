<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\CreateUserConsentQueryInterface;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class CreateUserConsentQuery implements CreateUserConsentQueryInterface
{
    public function __construct(
        private Connection $connection,
        private SqlPlatformHelperInterface $platformHelper,
    ) {
    }

    public function execute(int $userId, string $appId, array $authenticationScopes, \DateTimeImmutable $consentDate): void
    {
        $upsert = $this->platformHelper->upsertClause(
            ['user_id', 'app_id'],
            ['scopes = :scopes', 'consent_date = :consentDate']
        );

        $query = <<<SQL
            INSERT INTO akeneo_connectivity_user_consent (user_id, app_id, scopes, uuid, consent_date)
            VALUES (:userId, :appId, :scopes, :uuid, :consentDate)
            $upsert
            SQL;

        $this->connection->executeQuery($query, [
            'userId' => $userId,
            'appId' => $appId,
            'scopes' => \array_values($authenticationScopes),
            'uuid' => Uuid::uuid4(),
            'consentDate' => $consentDate,
        ], [
            'userId' => Types::INTEGER,
            'appId' => Types::STRING,
            'scopes' => Types::JSON,
            'uuid' => Types::ASCII_STRING,
            'consentDate' => Types::DATETIMETZ_IMMUTABLE,
        ]);
    }
}
