<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Infrastructure\Event\Subscriber;

use Akeneo\Platform\Installer\Infrastructure\Command\ZddMigration;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Webmozart\Assert\Assert;

/**
 * When installing a fresh new database, this subscriber will automatically mark ZDD Migrations as "migrated".
 *
 * @see ZddMigration
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
#[AsEventListener(event: InstallerEvents::POST_DB_CREATE, method: 'markMigrations')]
class MarkZddMigrationsAsMigratedSubscriber
{
    /** @var ZddMigration[] */
    private readonly array $zddMigrations;

    /**
     * @param iterable<mixed> $zddMigrations
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly SqlPlatformHelperInterface $platformHelper,
        iterable $zddMigrations,
    ) {
        Assert::allIsInstanceOf($zddMigrations, ZddMigration::class);
        $zddMigrations = $zddMigrations instanceof \Traversable ? \iterator_to_array($zddMigrations) : $zddMigrations;
        $this->zddMigrations = $zddMigrations;
    }

    public function markMigrations(): void
    {
        $upsert = $this->platformHelper->upsertClause(
            ['code'],
            ['status = ' . $this->platformHelper->insertedValue('status'), 'start_time = NOW()']
        );

        foreach ($this->zddMigrations as $zddMigration) {
            $this->connection->executeQuery(<<<SQL
                    INSERT INTO `pim_one_time_task` (`code`, `status`, `start_time`, `values`)
                    VALUES (:code, :status, NOW(), :values)
                    $upsert;
                SQL, [
                'code' => $this->getZddMigrationCode($zddMigration),
                'status' => 'finished',
                'values' => \json_encode((object) [], JSON_THROW_ON_ERROR),
            ]);
        }
    }

    private function getZddMigrationCode(ZddMigration $zddMigration): string
    {
        return \sprintf('zdd_%s', $zddMigration->getName());
    }
}
