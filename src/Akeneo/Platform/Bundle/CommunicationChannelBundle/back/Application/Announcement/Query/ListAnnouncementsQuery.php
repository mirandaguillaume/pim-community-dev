<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Application\Announcement\Query;

/**
 * @author Christophe Chausseray <chaauseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final readonly class ListAnnouncementsQuery
{
    public function __construct(private string $edition, private string $version, private string $locale, private int $userId, private ?string $searchAfter)
    {
    }

    public function edition(): string
    {
        return $this->edition;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function searchAfter(): ?string
    {
        return $this->searchAfter;
    }
}
