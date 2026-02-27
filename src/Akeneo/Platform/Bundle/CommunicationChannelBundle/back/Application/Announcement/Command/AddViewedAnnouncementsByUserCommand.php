<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Application\Announcement\Command;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final readonly class AddViewedAnnouncementsByUserCommand
{
    /**
     * @param string[] $viewedAnnouncementIds
     */
    public function __construct(private array $viewedAnnouncementIds, private int $userId)
    {
    }

    /**
     * @return string[]
     */
    public function viewedAnnouncementIds(): array
    {
        return $this->viewedAnnouncementIds;
    }

    public function userId(): int
    {
        return $this->userId;
    }
}
