<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\Sql;

use Akeneo\Category\Domain\Query\UpdateCategoryTemplateAttributesOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\ParameterType;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateCategoryTemplateAttributesOrderSql implements UpdateCategoryTemplateAttributesOrder
{
    public function __construct(
        private readonly Connection $connection,
    ) {}

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function fromAttributeCollection(AttributeCollection $attributeList): void
    {
        if ($attributeList->count() === 0) {
            return;
        }

        $queries = \implode(
            ';',
            \array_fill(
                0,
                $attributeList->count(),
                'UPDATE pim_catalog_category_attribute as pcca
                SET pcca.attribute_order = ?
                WHERE uuid = ?',
            ),
        );

        $statement = $this->connection->prepare(<<<SQL
                $queries
            SQL);

        $queryIndex = 0;
        foreach ($attributeList->getAttributes() as $attribute) {
            $statement->bindValue(++$queryIndex, $attribute->getOrder()->intValue(), ParameterType::INTEGER);
            $statement->bindValue(++$queryIndex, $attribute->getUuid()->toBytes(), ParameterType::STRING);
        }

        $statement->executeQuery();
    }
}
