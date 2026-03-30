<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Persistence\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\BuildSqlMaskField;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness\AttributeCase;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuildSqlMaskFieldTest extends TestCase
{
    private BuildSqlMaskField $sut;

    protected function setUp(): void
    {
    }

    public function test_it_returns_masks_when_no_attribute_case(): void
    {
        $platformHelper = $this->createMock(SqlPlatformHelperInterface::class);

        $platformHelper->method('conditional')->with('attribute.is_scopable', 'channel_locale.channel_code', "'<all_channels>'")->willReturn("IF(attribute.is_scopable, channel_locale.channel_code, '<all_channels>')");
        $platformHelper->method('conditional')->with('attribute.is_localizable', 'channel_locale.locale_code', "'<all_locales>'")->willReturn("IF(attribute.is_localizable, channel_locale.locale_code, '<all_locales>')");
        $this->sut = new BuildSqlMaskField([], $platformHelper);
        $sql = <<<SQL
                    JSON_ARRAYAGG(
                        CONCAT(
                            attribute.code,
                            '-',
                            IF(attribute.is_scopable, channel_locale.channel_code, '<all_channels>'),
                            '-',
                            IF(attribute.is_localizable, channel_locale.locale_code, '<all_locales>')
                        )
                    )
                    AS mask
                    SQL;
        $this->assertSame($sql, $this->sut->__invoke());
    }

    public function test_it_returns_masks_whith_attribute_cases(): void
    {
        $attributeTypeA = $this->createMock(AttributeCase::class);
        $attributeTypeB = $this->createMock(AttributeCase::class);
        $platformHelper = $this->createMock(SqlPlatformHelperInterface::class);

        $platformHelper->method('conditional')->with('attribute.is_scopable', 'channel_locale.channel_code', "'<all_channels>'")->willReturn("IF(attribute.is_scopable, channel_locale.channel_code, '<all_channels>')");
        $platformHelper->method('conditional')->with('attribute.is_localizable', 'channel_locale.locale_code', "'<all_locales>'")->willReturn("IF(attribute.is_localizable, channel_locale.locale_code, '<all_locales>')");
        $this->sut = new BuildSqlMaskField([
                    $attributeTypeA,
                    $attributeTypeB,
                ], $platformHelper);
        $attributeTypeA->method('getCase')->willReturn("WHEN attribute.attribute_type = 'typeA'
                    THEN 'TypeA'");
        $attributeTypeB->method('getCase')->willReturn("WHEN attribute.attribute_type = 'typeB'
                    THEN 'TypeB'");
        $sql = <<<SQL
                    JSON_ARRAYAGG(
                        CONCAT(
                            CASE
                                WHEN attribute.attribute_type = 'typeA'
                                    THEN 'TypeA'
                                WHEN attribute.attribute_type = 'typeB'
                                    THEN 'TypeB'
                                ELSE attribute.code
                            END,
                            '-',
                            IF(attribute.is_scopable, channel_locale.channel_code, '<all_channels>'),
                            '-',
                            IF(attribute.is_localizable, channel_locale.locale_code, '<all_locales>')
                        )
                    ) AS mask
                    SQL;
        $this->sut->__invoke()->shouldHaveSqlQueryEqualsTo($sql);
    }

    // TODO: Custom matchers from getMatchers() need manual conversion
}
