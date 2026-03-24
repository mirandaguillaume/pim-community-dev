<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Replacement for the removed DBAL 3 ArrayType.
 *
 * Stores and retrieves PHP arrays using serialize/unserialize.
 * Registered as type "array" to maintain backward compatibility with existing
 * entity mappings and database columns that contain PHP-serialized data.
 */
class PhpSerializedArrayType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return serialize($value);
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if ($value === null) {
            return null;
        }

        $value = \is_resource($value) ? stream_get_contents($value) : $value;

        // Support both PHP-serialized and JSON-encoded data for migration flexibility
        $val = @unserialize($value);

        if ($val === false && $value !== 'b:0;') {
            // Try JSON decode as fallback
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }

            return $value;
        }

        return $val;
    }
}
