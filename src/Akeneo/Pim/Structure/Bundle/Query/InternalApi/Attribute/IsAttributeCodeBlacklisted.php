<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\InternalApi\Attribute;

use Akeneo\Pim\Structure\Component\Query\InternalApi\IsAttributeCodeBlacklistedInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

final readonly class IsAttributeCodeBlacklisted implements IsAttributeCodeBlacklistedInterface
{
    public function __construct(private Connection $connection) {}

    public function execute(string $attributeCode): bool
    {
        $sql = <<<SQL
                    SELECT EXISTS(
                        SELECT 1 FROM pim_catalog_attribute_blacklist WHERE attribute_code = :attribute_code
                    ) as is_blacklisted
            SQL;

        $statement = $this->connection->executeQuery($sql, ['attribute_code' => $attributeCode]);
        $platform = $this->connection->getDatabasePlatform();
        $result = $statement->fetchAssociative();

        return Type::getType(Types::BOOLEAN)->convertToPhpValue($result['is_blacklisted'], $platform);
    }
}
