<?php

namespace Akeneo\Channel\Infrastructure\Query\Sql;

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\ConversionUnitCollection;
use Akeneo\Channel\API\Query\FindChannels;
use Akeneo\Channel\API\Query\LabelCollection;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class SqlFindChannels implements FindChannels
{
    public function __construct(
        private Connection $connection,
        private SqlPlatformHelperInterface $sql,
    ) {
    }

    /**
     * @return Channel[]
     */
    public function findAll(): array
    {
        $localeCodes = $this->sql->jsonRemoveKey(
            $this->sql->jsonObjectAgg("COALESCE(l.id, 'NO_LOCALE')", 'l.code'),
            'NO_LOCALE'
        );
        $labels = $this->sql->jsonRemoveKey(
            $this->sql->jsonObjectAgg("COALESCE(ct.locale, 'NO_LABEL')", 'ct.label'),
            'NO_LABEL'
        );
        $currencies = $this->sql->jsonRemoveKey(
            $this->sql->jsonObjectAgg("COALESCE(cur.id, 'NO_CURRENCY')", 'cur.code'),
            'NO_CURRENCY'
        );

        $sql = <<<SQL
                SELECT
                    c.code AS channelCode,
                    {$localeCodes} AS localeCodes,
                    {$labels} AS labels,
                    {$currencies} AS activatedCurrencies,
                    c.conversionUnits
                FROM pim_catalog_channel c
                LEFT JOIN pim_catalog_channel_locale cl
                    ON c.id = cl.channel_id
                LEFT JOIN pim_catalog_locale l
                    ON cl.locale_id = l.id
                LEFT JOIN pim_catalog_channel_translation ct
                    ON c.id = ct.foreign_key
                LEFT JOIN pim_catalog_channel_currency cc
                    ON c.id = cc.channel_id
                LEFT JOIN pim_catalog_currency cur
                    ON cc.currency_id = cur.id
                GROUP BY c.code;
            SQL;

        $results = $this->connection->executeQuery($sql)->fetchAllAssociative();
        $channels = [];

        foreach ($results as $result) {
            $channels[] = new Channel(
                $result['channelCode'],
                array_values(json_decode((string) $result['localeCodes'], true, 512, JSON_THROW_ON_ERROR)),
                LabelCollection::fromArray(json_decode((string) $result['labels'], true, 512, JSON_THROW_ON_ERROR)),
                array_values(json_decode((string) $result['activatedCurrencies'], true, 512, JSON_THROW_ON_ERROR)),
                ConversionUnitCollection::fromArray(unserialize($result['conversionUnits'], ['allowed_classes' => false])),
            );
        }

        return $channels;
    }
}
