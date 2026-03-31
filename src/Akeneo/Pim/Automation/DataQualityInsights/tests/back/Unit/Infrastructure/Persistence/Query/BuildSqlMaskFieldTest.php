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
    private function createPlatformHelper(): SqlPlatformHelperInterface|MockObject
    {
        $platformHelper = $this->createMock(SqlPlatformHelperInterface::class);
        $platformHelper->method('conditional')->willReturnCallback(
            function (string $condition, string $trueExpr, string $falseExpr): string {
                return "IF($condition, $trueExpr, $falseExpr)";
            }
        );
        return $platformHelper;
    }

    public function test_it_returns_masks_when_no_attribute_case(): void
    {
        $platformHelper = $this->createPlatformHelper();
        $sut = new BuildSqlMaskField([], $platformHelper);
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
        $this->assertSame($sql, $sut->__invoke());
    }

    public function test_it_returns_masks_whith_attribute_cases(): void
    {
        $attributeTypeA = $this->createMock(AttributeCase::class);
        $attributeTypeB = $this->createMock(AttributeCase::class);
        $platformHelper = $this->createPlatformHelper();
        $sut = new BuildSqlMaskField([
                    $attributeTypeA,
                    $attributeTypeB,
                ], $platformHelper);
        $attributeTypeA->method('getCase')->willReturn("WHEN attribute.attribute_type = 'typeA'
                    THEN 'TypeA'");
        $attributeTypeB->method('getCase')->willReturn("WHEN attribute.attribute_type = 'typeB'
                    THEN 'TypeB'");
        $result = $sut->__invoke();
        $this->assertStringContainsString('JSON_ARRAYAGG', $result);
        $this->assertStringContainsString("WHEN attribute.attribute_type = 'typeA'", $result);
        $this->assertStringContainsString("WHEN attribute.attribute_type = 'typeB'", $result);
        $this->assertStringContainsString('ELSE attribute.code', $result);
        $this->assertStringContainsString('AS mask', $result);
    }
}
