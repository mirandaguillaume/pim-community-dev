<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllFamilyCodesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\FamilyCode;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class GetAllFamilyCodesQuery implements GetAllFamilyCodesQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(): array
    {
        $query = <<<SQL
            SELECT code FROM pim_catalog_family;
            SQL;

        $statement = $this->connection->executeQuery($query);

        return array_map(fn ($row) => new FamilyCode($row['code']), $statement->fetchAllAssociative());
    }
}
