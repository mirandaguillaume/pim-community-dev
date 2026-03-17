<?php

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

/**
 * Stores dates with UTC timezone
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UTCDateTimeType extends DateTimeType
{
    private static ?\DateTimeZone $utc = null;

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        $value->setTimeZone(self::$utc ?: (self::$utc = new \DateTimeZone('UTC')));

        return parent::convertToDatabaseValue($value, $platform);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?\DateTime
    {
        if (null === $value || $value instanceof \DateTimeInterface) {
            return $value;
        }

        $val = \DateTime::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            self::$utc ?: (self::$utc = new \DateTimeZone('UTC'))
        );

        if (!$val) {
            // MySQL 8.4 returns datetime values with fractional seconds (e.g. "2024-01-01 00:00:00.000000")
            $val = \DateTime::createFromFormat(
                'Y-m-d H:i:s.u',
                $value,
                self::$utc ?: (self::$utc = new \DateTimeZone('UTC'))
            );
        }

        if (!$val) {
            throw ConversionException::conversionFailed($value, 'datetime');
        }

        $serverTimezone = date_default_timezone_get();
        $val->setTimezone(new \DateTimeZone($serverTimezone));

        $errors = \DateTime::getLastErrors();

        // date was parsed to completely not valid value
        if (\is_array($errors) && $errors['warning_count'] > 0 && (int) $val->format('Y') < 0) {
            return null;
        }

        return $val;
    }
}
