<?php

declare(strict_types=1);

namespace Akeneo\Platform\CommunicationChannel\Infrastructure\CommunicationChannel\Api;

use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Model\Read\AnnouncementItem;
use Akeneo\Platform\CommunicationChannel\Domain\Announcement\Query\FindAnnouncementItemsInterface;
use GuzzleHttp\Client;

/**
 * @author Christophe Chausseray <chausseray.christophe@gmail.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ApiFindAnnouncementItems implements FindAnnouncementItemsInterface
{
    private const BASE_URI = '/announcements';

    private readonly \GuzzleHttp\Client $client;

    public function __construct(string $apiUrl)
    {
        $this->client = new Client(['base_uri' => $apiUrl]);
    }

    public function byPimVersion(string $pimEdition, string $pimVersion, string $locale, ?string $searchAfter): array
    {
        $queryParameters = [
            'pim_edition' => $pimEdition,
            'pim_version' => $pimVersion,
            'locale' => $locale,
        ];
        if (null !== $searchAfter) {
            $queryParameters['search_after'] = $searchAfter;
        }

        $response = $this->client->request('GET', self::BASE_URI, ['query' => $queryParameters]);
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(
                sprintf(
                    'Error occurred when fetching the announcements with status code "%s". Please check the logs of the external service.',
                    $response->getStatusCode()
                )
            );
        }
        $content = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR)['data'];

        return array_map(fn ($announcement) => $this->getAnnouncementItem($announcement), array_values($content));
    }

    private function getAnnouncementItem(array $announcement): AnnouncementItem
    {
        return new AnnouncementItem(
            $announcement['id'],
            $announcement['title'],
            $announcement['description'],
            $announcement['img'] ?? null,
            $announcement['imgAlt'] ?? null,
            $announcement['link'],
            new \DateTimeImmutable($announcement['startDate']),
            new \DateTimeImmutable($announcement['notificationEndDate']),
            $announcement['tags']
        );
    }
}
