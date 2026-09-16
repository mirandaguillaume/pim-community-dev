<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\Model\Attribute\AttributeRichText;
use Akeneo\Category\Domain\Model\Attribute\AttributeText;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\Storage\Sql\GetCategoryTemplateAttributeSql;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCategoryTemplateAttributeSqlTest extends TestCase
{
    private const TEMPLATE_UUID = '02274dac-e99a-4e34-a9dd-a3e5b2c2d0f0';
    private const ATTRIBUTE_UUID_1 = '840fcd1a-f66b-4f0c-9bbd-596629362bfd';
    private const ATTRIBUTE_UUID_2 = 'e9b6cd15-6c65-4a29-b6cb-b1c11a8a0f27';
    private const ATTRIBUTE_UUID_3 = '1eaf1d2d-2b3e-4a1c-8e29-8d05e3a1c111';

    private Connection|MockObject $connection;
    private GetCategoryTemplateAttributeSql $sut;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->sut = new GetCategoryTemplateAttributeSql($this->connection);
    }

    public function testItIsAGetAttributeQuery(): void
    {
        $this->assertInstanceOf(GetAttribute::class, $this->sut);
    }

    public function testItFetchesTheActiveAttributesOfATemplateOrderedByAttributeOrder(): void
    {
        $templateUuid = TemplateUuid::fromString(self::TEMPLATE_UUID);
        $capturedSql = null;
        $capturedParams = null;
        $capturedTypes = null;

        $this->connection
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturnCallback(
                function (string $sql, array $params, array $types) use (&$capturedSql, &$capturedParams, &$capturedTypes): Result {
                    $capturedSql = $sql;
                    $capturedParams = $params;
                    $capturedTypes = $types;

                    return $this->resultReturningAll([
                        $this->attributeRow(self::ATTRIBUTE_UUID_1, 'title', 'text', 1),
                        $this->attributeRow(self::ATTRIBUTE_UUID_2, 'description', 'richtext', 2),
                    ]);
                },
            );

        $attributes = $this->sut->byTemplateUuid($templateUuid);

        // The template uuid must be sent as its binary representation, not as the human readable string.
        $this->assertSame(['template_uuid' => $templateUuid->toBytes()], $capturedParams);
        $this->assertSame(['template_uuid' => ParameterType::STRING], $capturedTypes);
        // Deactivated attributes must never be returned, and the template order must be preserved by SQL.
        $this->assertStringContainsString('is_deactivated = 0', $capturedSql);
        $this->assertStringContainsString('ORDER BY attribute_order', $capturedSql);

        $this->assertCount(2, $attributes);
        $this->assertInstanceOf(AttributeText::class, $attributes->getAttributes()[0]);
        $this->assertSame('title', (string) $attributes->getAttributes()[0]->getCode());
        $this->assertInstanceOf(AttributeRichText::class, $attributes->getAttributes()[1]);
        $this->assertSame('description', (string) $attributes->getAttributes()[1]->getCode());
    }

    public function testItReturnsAnEmptyCollectionWhenTheTemplateHasNoActiveAttribute(): void
    {
        $this->connection
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturn($this->resultReturningAll([]));

        $attributes = $this->sut->byTemplateUuid(TemplateUuid::fromString(self::TEMPLATE_UUID));

        $this->assertCount(0, $attributes);
        $this->assertSame([], $attributes->getAttributes());
    }

    public function testItBuildsOnePlaceholderPerUuidAndBindsThemPositionally(): void
    {
        $uuids = [
            AttributeUuid::fromString(self::ATTRIBUTE_UUID_1),
            AttributeUuid::fromString(self::ATTRIBUTE_UUID_2),
            AttributeUuid::fromString(self::ATTRIBUTE_UUID_3),
        ];
        $bindings = [];
        $capturedSql = null;

        $statement = $this->createMock(Statement::class);
        $statement
            ->method('bindValue')
            ->willReturnCallback(function ($param, $value, $type) use (&$bindings): void {
                $bindings[] = [$param, $value, $type];
            });
        $statement
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturn($this->resultReturningAll([
                $this->attributeRow(self::ATTRIBUTE_UUID_1, 'title', 'text', 1),
                $this->attributeRow(self::ATTRIBUTE_UUID_2, 'description', 'richtext', 2),
                $this->attributeRow(self::ATTRIBUTE_UUID_3, 'picture', 'image', 3),
            ]));

        $this->connection
            ->expects($this->once())
            ->method('prepare')
            ->willReturnCallback(function (string $sql) use (&$capturedSql, $statement): Statement {
                $capturedSql = $sql;

                return $statement;
            });

        $attributes = $this->sut->byUuids($uuids);

        $this->assertStringContainsString(
            'uuid IN (UUID_TO_BIN(?),UUID_TO_BIN(?),UUID_TO_BIN(?))',
            $capturedSql,
        );
        $this->assertSame(3, \substr_count($capturedSql, 'UUID_TO_BIN(?)'));
        $this->assertStringContainsString('is_deactivated = 0', $capturedSql);

        // Positional binding starts at 1 and follows the order of the given uuids.
        $this->assertSame(
            [
                [1, self::ATTRIBUTE_UUID_1, ParameterType::STRING],
                [2, self::ATTRIBUTE_UUID_2, ParameterType::STRING],
                [3, self::ATTRIBUTE_UUID_3, ParameterType::STRING],
            ],
            $bindings,
        );

        $this->assertCount(3, $attributes);
    }

    public function testItBindsNothingAndBuildsAnEmptyInClauseWhenNoUuidIsGiven(): void
    {
        // Characterisation of a production defect: an empty uuid list produces `uuid IN ()`,
        // which MySQL rejects as a syntax error instead of short-circuiting to an empty collection.
        $capturedSql = null;
        $statement = $this->createMock(Statement::class);
        $statement->expects($this->never())->method('bindValue');
        $statement->method('executeQuery')->willReturn($this->resultReturningAll([]));

        $this->connection
            ->method('prepare')
            ->willReturnCallback(function (string $sql) use (&$capturedSql, $statement): Statement {
                $capturedSql = $sql;

                return $statement;
            });

        $attributes = $this->sut->byUuids([]);

        $this->assertStringContainsString('uuid IN ()', $capturedSql);
        $this->assertCount(0, $attributes);
    }

    public function testItReturnsTheFirstMatchingAttributeByUuid(): void
    {
        $statement = $this->createMock(Statement::class);
        $statement->method('executeQuery')->willReturn($this->resultReturningAll([
            $this->attributeRow(self::ATTRIBUTE_UUID_1, 'title', 'text', 1),
        ]));
        $this->connection->method('prepare')->willReturn($statement);

        $attribute = $this->sut->byUuid(AttributeUuid::fromString(self::ATTRIBUTE_UUID_1));

        $this->assertInstanceOf(Attribute::class, $attribute);
        $this->assertSame(self::ATTRIBUTE_UUID_1, (string) $attribute->getUuid());
        $this->assertSame('title', (string) $attribute->getCode());
    }

    public function testItReturnsNullWhenTheAttributeUuidMatchesNoActiveAttribute(): void
    {
        $statement = $this->createMock(Statement::class);
        $statement->method('executeQuery')->willReturn($this->resultReturningAll([]));
        $this->connection->method('prepare')->willReturn($statement);

        $this->assertNull($this->sut->byUuid(AttributeUuid::fromString(self::ATTRIBUTE_UUID_1)));
    }

    public function testItFetchesAnActiveAttributeByCode(): void
    {
        $capturedSql = null;
        $capturedParams = null;
        $capturedTypes = null;

        $this->connection
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturnCallback(
                function (string $sql, array $params, array $types) use (&$capturedSql, &$capturedParams, &$capturedTypes): Result {
                    $capturedSql = $sql;
                    $capturedParams = $params;
                    $capturedTypes = $types;

                    return $this->resultReturningOne(
                        $this->attributeRow(self::ATTRIBUTE_UUID_1, 'title', 'text', 1),
                    );
                },
            );

        $attribute = $this->sut->byCode(new AttributeCode('title'));

        $this->assertSame(['code' => 'title'], $capturedParams);
        $this->assertSame(['code' => ParameterType::STRING], $capturedTypes);
        $this->assertStringContainsString('is_deactivated = 0', $capturedSql);
        $this->assertSame('title', (string) $attribute->getCode());
        $this->assertSame(self::TEMPLATE_UUID, (string) $attribute->getTemplateUuid());
    }

    public function testItFailsHardWhenNoActiveAttributeMatchesTheCode(): void
    {
        // Characterisation of a production defect: `byCode()` forwards the `false` returned by
        // `fetchAssociative()` straight to `Attribute::fromDatabase(array $result)`. There is no
        // not-found handling, so an unknown or deactivated code crashes instead of returning null.
        $this->connection
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturn($this->resultReturningNone());

        $this->expectException(\TypeError::class);

        $this->sut->byCode(new AttributeCode('unknown_code'));
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function resultReturningAll(array $rows): Result|MockObject
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn($rows);

        return $result;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function resultReturningOne(array $row): Result|MockObject
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturn($row);

        return $result;
    }

    private function resultReturningNone(): Result|MockObject
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturn(false);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function attributeRow(string $uuid, string $code, string $type, int $order): array
    {
        return [
            'uuid' => $uuid,
            'code' => $code,
            'category_template_uuid' => self::TEMPLATE_UUID,
            'labels' => \json_encode(['en_US' => \ucfirst($code)], JSON_THROW_ON_ERROR),
            'attribute_type' => $type,
            'attribute_order' => $order,
            'is_required' => 0,
            'is_scopable' => 0,
            'is_localizable' => 1,
            'additional_properties' => \json_encode([], JSON_THROW_ON_ERROR),
        ];
    }
}
