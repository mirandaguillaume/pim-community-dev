<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Install;

use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Doctrine\DBAL\Connection as DbalConnection;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: InstallerEvents::POST_DB_CREATE, method: 'updateSchema', priority: -10)]
class InstallSubscriber
{
    public function __construct(private readonly DbalConnection $dbalConnection)
    {
    }

    public function updateSchema(): void
    {
        $this->dbalConnection->executeStatement(CreateConnectionAuditTableQuery::QUERY);
        $this->dbalConnection->executeStatement(CreateConnectionAuditErrorTableQuery::QUERY);
    }
}
