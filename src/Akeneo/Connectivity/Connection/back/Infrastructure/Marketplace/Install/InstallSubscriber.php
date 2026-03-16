<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Install;

use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysHandler;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Doctrine\DBAL\Connection as DbalConnection;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: InstallerEvents::POST_DB_CREATE, method: 'updateSchema', priority: -10)]
#[AsEventListener(event: InstallerEvents::POST_LOAD_FIXTURES, method: 'loadFixtures', priority: -20)]
class InstallSubscriber
{
    public function __construct(
        private readonly DbalConnection $dbalConnection,
        private readonly GenerateAsymmetricKeysHandler $generateAsymmetricKeysHandler,
    ) {
    }

    public function updateSchema(): void
    {
        $this->dbalConnection->executeStatement(CreateTestAppTableQuery::QUERY);
    }

    public function loadFixtures(): void
    {
        $this->generateAsymmetricKeysHandler->handle(new GenerateAsymmetricKeysCommand());
    }
}
