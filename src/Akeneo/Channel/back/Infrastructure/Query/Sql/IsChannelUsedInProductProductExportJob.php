<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Query\Sql;

use Akeneo\Channel\Infrastructure\Component\Query\IsChannelUsedInProductExportJobInterface;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class IsChannelUsedInProductProductExportJob implements IsChannelUsedInProductExportJobInterface
{
    public function __construct(
        private Connection $dbConnection,
        private SqlPlatformHelperInterface $sql,
        private array $productExportJobNames,
    ) {
    }

    public function execute(string $channelCode): bool
    {
        $isChannelUsedRegex = sprintf('scope[{";:as0-9]+\b%s\b.+', preg_quote($channelCode, '/'));
        $regexpClause = $this->sql->regexpMatch('raw_parameters', ':regex');

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
                'regex' => ParameterType::STRING,
            ]
        )->fetchOne();

        return (bool) $result;
    }
}
