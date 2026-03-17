<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Query;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

class SqlUpdateJobExecutionStatus
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function updateByJobExecutionId(int $jobExecutionId, BatchStatus $batchStatus): void
    {
        $sql = <<<SQL
            UPDATE akeneo_batch_job_execution
            SET `status` = :batch_status, `exit_code` = :exit_code
            WHERE `id` = :job_execution_id
            SQL;
        $this->connection->executeStatement(
            $sql,
            [
                'batch_status'     => $batchStatus->getValue(),
                'exit_code'        => $batchStatus->__toString(),
                'job_execution_id' => $jobExecutionId,
            ],
            [
                'batch_status'     => ParameterType::INTEGER,
                'exit_code'        => ParameterType::STRING,
                'job_execution_id' => ParameterType::INTEGER,
            ]
        );
    }
}
