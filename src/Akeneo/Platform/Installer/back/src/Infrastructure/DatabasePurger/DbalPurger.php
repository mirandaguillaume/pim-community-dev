<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\DatabasePurger;

use Akeneo\Platform\Installer\Domain\Service\DatabasePurgerInterface;
use Doctrine\DBAL\Connection;

class DbalPurger implements DatabasePurgerInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function purge(array $tablesToReset): void
    {
        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tablesToReset as $table) {
            $this->connection->executeStatement(sprintf('TRUNCATE TABLE %s', $table));
        }
        $this->connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
