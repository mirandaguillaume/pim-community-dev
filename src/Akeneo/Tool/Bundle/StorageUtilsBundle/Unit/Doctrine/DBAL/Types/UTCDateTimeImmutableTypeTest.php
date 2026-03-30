<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\DBAL\Types;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\DBAL\Types\UTCDateTimeImmutableType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UTCDateTimeImmutableTypeTest extends TestCase
{
    private UTCDateTimeImmutableType $sut;

    protected function setUp(): void
    {
        $this->sut = new UTCDateTimeImmutableType();
        UTCDateTimeImmutableType::$defaultTimeZone = new \DateTimeZone('America/Los_Angeles');
    }

    public function test_it_converts_a_timezoned_immutable_date_time_to_a_utc_date_string(): void
    {
        $platform = $this->createMock(AbstractPlatform::class);

        $value = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2021-01-01T00:00:00+12:00');
        $platform->method('getDateTimeFormatString')->willReturn('Y-m-d H:i:s');
        $expected = '2020-12-31 12:00:00';
        $this->assertSame($expected, $this->sut->convertToDatabaseValue($value, $platform));
    }

    public function test_it_throws_if_the_date_time_is_not_immutable(): void
    {
        $platform = $this->createMock(AbstractPlatform::class);

        $value = \DateTime::createFromFormat(\DateTimeInterface::ATOM, '2021-01-01T00:00:00+12:00');
        $this->expectException(InvalidType::class);
        $this->sut->convertToDatabaseValue($value, $platform);
    }

    public function test_it_converts_a_utc_date_string_to_a_timezoned_immutable_date_time(): void
    {
        $platform = $this->createMock(AbstractPlatform::class);

        $value = '2021-01-01 00:00:00';
        $platform->method('getDateTimeFormatString')->willReturn('Y-m-d H:i:s');
        $expected = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2020-12-31T16:00:00-08:00');
        $this->assertEquals($expected, $this->sut->convertToPHPValue($value, $platform));
    }

    public function test_it_throws_if_the_date_string_format_is_invalid(): void
    {
        $platform = $this->createMock(AbstractPlatform::class);

        $value = 'not_a_valid_date_format';
        $platform->method('getDateTimeFormatString')->willReturn('Y-m-d H:i:s');
        $this->expectException(InvalidFormat::class);
        $this->sut->convertToPHPValue($value, $platform);
    }

    public function test_it_converts_a_utc_date_string_with_microseconds_to_a_timezoned_immutable_date_time(): void
    {
        $platform = $this->createMock(AbstractPlatform::class);

        // MySQL 8.4 returns datetime values with fractional seconds
        $value = '2021-01-01 00:00:00.000000';
        $platform->method('getDateTimeFormatString')->willReturn('Y-m-d H:i:s');
        $expected = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2020-12-31T16:00:00-08:00');
        $this->assertEquals($expected, $this->sut->convertToPHPValue($value, $platform));
    }

    public function test_it_doesnt_convert_null_values(): void
    {
        $platform = $this->createMock(AbstractPlatform::class);

        $this->assertNull($this->sut->convertToDatabaseValue(null, $platform));
        $this->assertNull($this->sut->convertToPHPValue(null, $platform));
    }
}
