<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Persistence;

use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Doctrine\DBAL\Connection;

/**
 * Retrieve all hours from events that are not yet complete.
 * I.e., the last update happened before the end of the event and need to be updated again.
 *
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectHourlyIntervalsToRefreshQuery
{
    public function __construct(private readonly Connection $dbalConnection)
    {
    }

    /**
     * @return HourlyInterval[]
     */
    public function execute(): array
    {
        $selectSQL = <<<SQL
            SELECT DISTINCT event_datetime FROM akeneo_connectivity_connection_audit_product
            WHERE updated < DATE_ADD(event_datetime, INTERVAL 1 HOUR) ORDER BY event_datetime
            SQL;
        $dateTimes = $this->dbalConnection->executeQuery($selectSQL)->fetchFirstColumn();

        $format = $this->dbalConnection->getDatabasePlatform()->getDateTimeFormatString();

        return \array_map(fn (string $dateTime): HourlyInterval => HourlyInterval::createFromDateTime(
            \DateTimeImmutable::createFromFormat($format, $dateTime, new \DateTimeZone('UTC'))
                ?: \DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $dateTime, new \DateTimeZone('UTC'))
        ), $dateTimes);
    }
}
