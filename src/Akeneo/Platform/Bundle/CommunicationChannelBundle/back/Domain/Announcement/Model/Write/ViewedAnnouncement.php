<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Write;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final readonly class ViewedAnnouncement
{
    private function __construct(private string $announcementId, private int $userId) {}

    public static function create(string $announcementId, int $userId): self
    {
        return new self($announcementId, $userId);
    }

    public function announcementId(): string
    {
        return $this->announcementId;
    }

    public function userId(): int
    {
        return $this->userId;
    }
}
