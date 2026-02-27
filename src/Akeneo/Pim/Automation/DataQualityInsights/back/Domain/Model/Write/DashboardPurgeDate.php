<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DashboardPurgeDate
{
    public function __construct(private readonly TimePeriod $period, private readonly ConsolidationDate $date) {}

    public function getPeriod(): TimePeriod
    {
        return $this->period;
    }

    public function getDate(): ConsolidationDate
    {
        return $this->date;
    }
}
