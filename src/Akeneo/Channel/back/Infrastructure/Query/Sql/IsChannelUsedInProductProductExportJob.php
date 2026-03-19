<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Query\Sql;

use Akeneo\Channel\Infrastructure\Component\Query\IsChannelUsedInProductExportJobInterface;
use Akeneo\Tool\Component\StorageUtils\Database\DatabasePlatformTrait;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class IsChannelUsedInProductProductExportJob implements IsChannelUsedInProductExportJobInterface
{
    use DatabasePlatformTrait;

    public function __construct(private Connection $dbConnection, private array $productExportJobNames)
    {
    }

    private function getConnection(): Connection
    {
        return $this->dbConnection;
    }

    public function execute(string $channelCode): bool
    {
        $isChannelUsedRegex = sprintf('scope[{";:as0-9]+\b%s\b.+', preg_quote($channelCode, '/'));
        $regexpClause = $this->regexpMatch('raw_parameters', ':regex');

        $query = <<<SQL
            SELECT 1
            FROM akeneo_batch_job_instance
            WHERE job_name IN (:jobNames)
                AND {$regexpClause};
            SQL;

        $result = $this->dbConnection->executeQuery(
            $query,
            [
                'jobNames' => $this->productExportJobNames,
                'regex' => $isChannelUsedRegex,
            ],
            [
                'jobNames' => ArrayParameterType::STRING,
                'regex' => \Doctrine\DBAL\ParameterType::STRING,
            ]
        )->fetchOne();

        return (bool) $result;
    }
}
