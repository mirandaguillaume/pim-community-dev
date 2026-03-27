<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Query\DeleteCategoryTreeTemplateByTemplateUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteCategoryTreeTemplateByTemplateUuidSql implements DeleteCategoryTreeTemplateByTemplateUuid
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(TemplateUuid $templateUuid): void
    {
        $this->connection->transactional(function (Connection $connection) use ($templateUuid) {
            $connection->executeQuery(
                <<<SQL
                        DELETE FROM pim_catalog_category_tree_template
                        WHERE category_template_uuid = :template_uuid
                    SQL,
                [
                    'template_uuid' => $templateUuid->toBytes(),
                ],
                [
                    'template_uuid' => ParameterType::STRING,
                ],
            );

            $connection->executeQuery(
                <<<SQL
                        DELETE FROM pim_catalog_category_attribute
                        WHERE category_template_uuid = :template_uuid
                    SQL,
                [
                    'template_uuid' => $templateUuid->toBytes(),
                ],
                [
                    'template_uuid' => ParameterType::STRING,
                ],
            );

            $connection->executeQuery(
                <<<SQL
                        DELETE FROM pim_catalog_category_template
                        WHERE uuid = :template_uuid
                    SQL,
                [
                    'template_uuid' => $templateUuid->toBytes(),
                ],
                [
                    'template_uuid' => ParameterType::STRING,
                ],
            );
        });
    }
}
