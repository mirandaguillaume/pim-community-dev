<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\CommunicationChannel\LocalFilestorage;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindNewAnnouncementIdsInterface;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final readonly class LocalFilestorageFindNewAnnouncementIds implements FindNewAnnouncementIdsInterface
{
    private const FILENAME = 'serenity-updates.json';

    private string|bool $externalJson;

    public function __construct()
    {
        $this->externalJson = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . self::FILENAME);
    }

    public function find(string $pimEdition, string $pimVersion, string $locale): array
    {
        $content = json_decode($this->externalJson, true, 512, JSON_THROW_ON_ERROR);

        $currentDate = new \DateTimeImmutable();

        $newAnnouncementIds = [];
        foreach ($content['data'] as $announcement) {
            $startDate = new \DateTimeImmutable($announcement['startDate']);
            $endDate = new \DateTimeImmutable($announcement['notificationEndDate']);
            if ($currentDate > $startDate && $currentDate < $endDate) {
                $newAnnouncementIds[] = $announcement['id'];
            }
        }

        return $newAnnouncementIds;
    }
}
