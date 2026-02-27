<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Job\Infrastructure\Query;

use Akeneo\Platform\Job\Application\DeleteJobInstance\DeleteJobInstanceInterface;
use Akeneo\Platform\Job\ServiceApi\JobInstance\DeleteJobInstance\CannotDeleteJobInstanceException;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

final readonly class DeleteJobInstance implements DeleteJobInstanceInterface
{
    public function __construct(
        private Connection $connection,
    ) {}

    /**
     * @param string[] $codes
     */
    public function byCodes(array $codes): void
    {
        $this->checkJobsExist($codes);

        $sql = <<<SQL
                DELETE FROM akeneo_batch_job_instance WHERE code IN (:codes)
            SQL;

        $this->connection->executeStatement($sql, ['codes' => $codes], ['codes' => ArrayParameterType::STRING]);
    }

    public function checkJobsExist(array $codes): void
    {
        $sql = <<<SQL
                SELECT code FROM akeneo_batch_job_instance WHERE code IN (:codes)
            SQL;

        $existingJobCodes = $this->connection->executeQuery(
            $sql,
            ['codes' => $codes],
            ['codes' => ArrayParameterType::STRING],
        )->fetchFirstColumn();

        foreach ($codes as $code) {
            if (!in_array($code, $existingJobCodes)) {
                throw CannotDeleteJobInstanceException::notFound($code);
            }
        }
    }
}
