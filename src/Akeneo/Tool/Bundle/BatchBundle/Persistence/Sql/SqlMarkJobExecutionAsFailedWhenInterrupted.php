<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Query\MarkJobExecutionAsFailedWhenInterrupted;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

final readonly class SqlMarkJobExecutionAsFailedWhenInterrupted implements MarkJobExecutionAsFailedWhenInterrupted
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(array $jobCodes): int
    {
        $sql = <<<SQL
UPDATE akeneo_batch_job_execution job_execution
INNER JOIN akeneo_batch_job_instance job_instance ON job_execution.job_instance_id = job_instance.id
SET job_execution.status = :failedStatus, job_execution.exit_code = :failedExitCode
WHERE job_instance.code IN (:jobCodes)
AND job_execution.health_check_time IS NULL
AND job_execution.status IN (:runningStatuses);
SQL;

        return $this->connection->executeStatement(
            $sql,
            [
                'jobCodes' => $jobCodes,
                'failedStatus' => BatchStatus::FAILED,
                'failedExitCode' => ExitStatus::FAILED,
                'runningStatuses' => [BatchStatus::STARTED, BatchStatus::STOPPING],
            ],
            [
                'jobCodes' => ArrayParameterType::STRING,
                'runningStatuses' => ArrayParameterType::INTEGER,
            ]
        );
    }
}
