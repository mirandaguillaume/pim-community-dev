<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UTCDateTimeImmutableType extends DateTimeImmutableType
{
    public static ?\DateTimeZone $defaultTimeZone = null;

    private static ?\DateTimeZone $utc = null;

    #[\Override]
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof \DateTimeImmutable) {
            $value = $value->setTimeZone(self::getUtc());
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?\DateTimeImmutable
    {
        if (null === $value || $value instanceof \DateTimeImmutable) {
            return $value;
        }

        $converted = \DateTimeImmutable::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            self::getUtc()
        );

        if (!$converted) {
            // MySQL 8.4 returns datetime values with fractional seconds (e.g. "2024-01-01 00:00:00.000000")
            $converted = \DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s.u',
                $value,
                self::getUtc()
            );
        }

        if (!$converted) {
            throw InvalidFormat::new(
                $value,
                'datetime_immutable',
                $platform->getDateTimeFormatString()
            );
        }

        $converted = $converted->setTimezone(self::getDefaultTimeZone());

        return $converted;
    }

    private static function getUtc(): \DateTimeZone
    {
        return self::$utc ?: self::$utc = new \DateTimeZone('UTC');
    }

    private static function getDefaultTimeZone(): \DateTimeZone
    {
        return self::$defaultTimeZone ?: self::$defaultTimeZone = new \DateTimeZone(date_default_timezone_get());
    }
}
