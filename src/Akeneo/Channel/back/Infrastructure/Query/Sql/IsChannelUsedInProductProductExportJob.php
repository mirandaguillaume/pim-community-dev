<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Query\Sql;

use Akeneo\Channel\Infrastructure\Component\Query\IsChannelUsedInProductExportJobInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class IsChannelUsedInProductProductExportJob implements IsChannelUsedInProductExportJobInterface
{
    public function __construct(private Connection $dbConnection, private array $productExportJobNames)
    {
    }

    public function execute(string $channelCode): bool
    {
        $isChannelUsedRegex = sprintf('scope[{";:as0-9]+\\\b%s\\\b.+', $channelCode);

        $query = <<<SQL
SELECT 1 
FROM akeneo_batch_job_instance
WHERE job_name IN (:jobNames)
    AND raw_parameters REGEXP '$isChannelUsedRegex';
SQL;

        $result = $this->dbConnection->executeQuery(
            $query,
            ['jobNames' => $this->productExportJobNames],
            ['jobNames' => ArrayParameterType::STRING]
        )->fetchOne();

        return boolval($result);
    }
}
