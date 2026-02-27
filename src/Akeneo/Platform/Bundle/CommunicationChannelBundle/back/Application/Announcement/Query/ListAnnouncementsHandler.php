<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Application\Announcement\Query;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read\AnnouncementItem;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindAnnouncementItemsInterface;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindViewedAnnouncementIdsInterface;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final readonly class ListAnnouncementsHandler
{
    public function __construct(private FindAnnouncementItemsInterface $findAnnouncementItems, private FindViewedAnnouncementIdsInterface $findViewedAnnouncementIds)
    {
    }

    /**
     * @return AnnouncementItem[]
     */
    public function execute(ListAnnouncementsQuery $query): array
    {
        $announcementItems = $this->findAnnouncementItems->byPimVersion($query->edition(), $query->version(), $query->locale(), $query->searchAfter());
        $viewedAnnouncementIds = $this->findViewedAnnouncementIds->byUserId($query->userId());

        $announcementItemsWithNew = [];
        foreach ($announcementItems as $announcementItem) {
            $announcementItemsWithNew[] =  $announcementItem->shouldBeNotified($viewedAnnouncementIds) ? $announcementItem->toNotify() : $announcementItem;
        }

        return $announcementItemsWithNew;
    }
}
