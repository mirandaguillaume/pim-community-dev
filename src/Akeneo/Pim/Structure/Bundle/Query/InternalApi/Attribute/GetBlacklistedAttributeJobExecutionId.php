<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\InternalApi\Attribute;

use Akeneo\Pim\Structure\Component\Query\InternalApi\GetBlacklistedAttributeJobExecutionIdInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

final readonly class GetBlacklistedAttributeJobExecutionId implements GetBlacklistedAttributeJobExecutionIdInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function forAttributeCode(string $attributeCode): ?int
    {
        $sql = <<<SQL
            SELECT cleanup_job_execution_id
            FROM `pim_catalog_attribute_blacklist`
            WHERE attribute_code = :attribute_code;
            SQL;

        $result = $this->connection->executeQuery(
            $sql,
            [
                'attribute_code' => $attributeCode,
            ],
            [
                'attribute_code' => ParameterType::STRING,
            ]
        )->fetchOne();

        if (false === $result) {
            return null;
        }

        return (int) $result;
    }
}
