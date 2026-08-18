<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\IsTemplateDeactivated;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\Storage\Sql\IsTemplateDeactivatedSql;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsTemplateDeactivatedSqlTest extends TestCase
{
    private const TEMPLATE_UUID = '02274dac-e99a-4e34-a9dd-a3e5b2c2d0f0';

    private Connection|MockObject $connection;
    private IsTemplateDeactivatedSql $sut;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->sut = new IsTemplateDeactivatedSql($this->connection);
    }

    public function testItIsAnIsTemplateDeactivatedQuery(): void
    {
        $this->assertInstanceOf(IsTemplateDeactivated::class, $this->sut);
    }

    public function testItQueriesTheTemplateTableWithTheBinaryUuid(): void
    {
        $templateUuid = TemplateUuid::fromString(self::TEMPLATE_UUID);
        $capturedSql = null;
        $capturedParams = null;

        $this->connection
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturnCallback(
                function (string $sql, array $params) use (&$capturedSql, &$capturedParams): Result {
                    $capturedSql = $sql;
                    $capturedParams = $params;

                    return $this->resultReturning(['is_deactivated' => '1']);
                },
            );

        $this->sut->__invoke($templateUuid);

        // The uuid must be sent as its binary representation, not as the human readable string.
        $this->assertSame(['template_uuid' => $templateUuid->toBytes()], $capturedParams);
        $this->assertStringContainsString('pim_catalog_category_template', $capturedSql);
        $this->assertStringContainsString('WHERE uuid = :template_uuid', $capturedSql);
    }

    public function testItReturnsTrueWhenTheTemplateIsDeactivated(): void
    {
        $this->connection
            ->method('executeQuery')
            ->willReturn($this->resultReturning(['is_deactivated' => '1']));

        $this->assertTrue($this->sut->__invoke(TemplateUuid::fromString(self::TEMPLATE_UUID)));
    }

    public function testItReturnsFalseWhenTheTemplateIsActive(): void
    {
        $this->connection
            ->method('executeQuery')
            ->willReturn($this->resultReturning(['is_deactivated' => '0']));

        $this->assertFalse($this->sut->__invoke(TemplateUuid::fromString(self::TEMPLATE_UUID)));
    }

    public function testItOnlyRecognisesTheDeactivatedFlagAsTheStringOne(): void
    {
        // Characterisation of a production fragility: the comparison is a strict `=== '1'`.
        // A driver or a DBAL configuration returning a native int (or bool) for the TINYINT column
        // silently reports every deactivated template as active. This test locks the current
        // behaviour so that such a driver change cannot pass unnoticed.
        $this->connection
            ->method('executeQuery')
            ->willReturn($this->resultReturning(['is_deactivated' => 1]));

        $this->assertFalse($this->sut->__invoke(TemplateUuid::fromString(self::TEMPLATE_UUID)));
    }

    public function testItRaisesAPhpWarningWhenTheTemplateDoesNotExist(): void
    {
        // Characterisation of a production defect: `fetchAssociative()` returns `false` for an
        // unknown template, and the result is dereferenced as an array without any check.
        // PHP emits a warning and the method reports the template as active.
        $this->connection
            ->method('executeQuery')
            ->willReturn($this->resultReturningNoRow());

        $raisedErrors = [];
        \set_error_handler(static function (int $severity, string $message) use (&$raisedErrors): bool {
            $raisedErrors[] = $message;

            return true;
        });

        try {
            $isDeactivated = $this->sut->__invoke(TemplateUuid::fromString(self::TEMPLATE_UUID));
        } finally {
            \restore_error_handler();
        }

        $this->assertFalse($isDeactivated);
        $this->assertCount(1, $raisedErrors);
        $this->assertStringContainsString('Trying to access array offset on', $raisedErrors[0]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function resultReturning(array $row): Result|MockObject
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturn($row);

        return $result;
    }

    private function resultReturningNoRow(): Result|MockObject
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturn(false);

        return $result;
    }
}
