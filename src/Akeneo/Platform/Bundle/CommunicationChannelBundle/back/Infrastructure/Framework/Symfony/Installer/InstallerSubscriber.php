<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\Framework\Symfony\Installer;

use Akeneo\Platform\CommunicationChannel\Infrastructure\Framework\Symfony\Installer\Query\CreateViewedAnnouncementsTableQuery;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Doctrine\DBAL\Connection as DbalConnection;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @author    Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: InstallerEvents::POST_DB_CREATE, method: 'createCommunicationChannelTable')]
class InstallerSubscriber
{
    public function __construct(private readonly DbalConnection $dbalConnection)
    {
    }

    public function createCommunicationChannelTable(): void
    {
        $this->dbalConnection->executeStatement(CreateViewedAnnouncementsTableQuery::QUERY);
    }
}
