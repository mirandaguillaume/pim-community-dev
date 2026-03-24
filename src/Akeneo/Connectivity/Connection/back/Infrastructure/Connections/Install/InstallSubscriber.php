<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Connections\Install;

use Akeneo\Connectivity\Connection\Infrastructure\Connections\WrongCredentialsCombination\Install\CreateWrongCredentialsCombinationQuery;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvent;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Doctrine\DBAL\Connection as DbalConnection;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
#[AsEventListener(event: InstallerEvents::POST_DB_CREATE, method: 'updateSchema', priority: 10)]
#[AsEventListener(event: InstallerEvents::POST_LOAD_FIXTURES, method: 'loadFixtures', priority: -10)]
class InstallSubscriber
{
    final public const string ICECAT_DEMO_DEV = 'icecat_demo_dev';

    public function __construct(
        private readonly DbalConnection $dbalConnection,
        private readonly FixturesLoader $fixturesLoader,
    ) {
    }

    public function updateSchema(): void
    {
        $this->dbalConnection->executeStatement(CreateConnectionTableQuery::QUERY);
        $this->dbalConnection->executeStatement(CreateWrongCredentialsCombinationQuery::QUERY);
    }

    public function loadFixtures(InstallerEvent $installerEvent): void
    {
        if (!\str_ends_with((string) $installerEvent->getArgument('catalog'), self::ICECAT_DEMO_DEV)) {
            return;
        }

        $this->fixturesLoader->loadFixtures();
    }
}
