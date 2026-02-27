<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CachedGetLocalesByChannelQuery implements GetLocalesByChannelQueryInterface
{
    private ?array $cachedChannelLocaleArray = null;

    private ?\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection $cachedChannelLocaleCollection = null;

    public function __construct(
        /** * @var Connection */
        private readonly Connection $db
    ) {}

    public function getArray(): array
    {
        if (null !== $this->cachedChannelLocaleArray) {
            return $this->cachedChannelLocaleArray;
        }

        $query = <<<SQL
            SELECT channel.code AS channelCode, locale.code AS localeCode
            FROM pim_catalog_channel_locale
            INNER JOIN pim_catalog_channel channel on pim_catalog_channel_locale.channel_id = channel.id
            INNER JOIN pim_catalog_locale locale on pim_catalog_channel_locale.locale_id = locale.id
            ORDER BY channelCode, localeCode;
            SQL;

        $statement = $this->db->executeQuery($query);

        $channelsLocales = [];
        foreach ($statement->fetchAllAssociative() as $channelLocale) {
            $channelsLocales[$channelLocale['channelCode']][] = $channelLocale['localeCode'];
        }

        $this->cachedChannelLocaleArray = $channelsLocales;

        return $channelsLocales;
    }

    public function getChannelLocaleCollection(): ChannelLocaleCollection
    {
        if (null === $this->cachedChannelLocaleCollection) {
            $channelsLocales = $this->getArray();
            $this->cachedChannelLocaleCollection = new ChannelLocaleCollection($channelsLocales);
        }

        return $this->cachedChannelLocaleCollection;
    }
}
