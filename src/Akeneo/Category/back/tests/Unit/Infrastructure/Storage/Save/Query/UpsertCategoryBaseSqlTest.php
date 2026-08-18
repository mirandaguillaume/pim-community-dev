<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Query\IsTemplateDeactivated;
use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryBase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Akeneo\Category\Infrastructure\Storage\Save\Query\UpsertCategoryBaseSql;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpsertCategoryBaseSqlTest extends TestCase
{
    private const TEMPLATE_UUID = '02274dac-e99a-4e34-a09a-8ecccbfa8c02';
    private const TITLE_UUID = '69e251b3-b876-48b5-9c09-92f54bfb528d';
    private const SUBTITLE_UUID = '8587cfd4-0f6d-4b0d-9d2c-e8a4f2e1e4f5';

    private Connection|MockObject $connection;
    private GetCategoryInterface|MockObject $getCategory;
    private IsTemplateDeactivated|MockObject $isTemplateDeactivated;
    private UpsertCategoryBaseSql $sut;

    /**
     * @var array<int, array{sql: string, params: array<string, mixed>, types: array<string, mixed>}>
     */
    private array $executedQueries = [];

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->getCategory = $this->createMock(GetCategoryInterface::class);
        $this->isTemplateDeactivated = $this->createMock(IsTemplateDeactivated::class);
        $this->executedQueries = [];

        $this->sut = new UpsertCategoryBaseSql(
            $this->connection,
            $this->getCategory,
            $this->isTemplateDeactivated,
        );
    }

    public function testItIsAnUpsertCategoryBase(): void
    {
        $this->assertInstanceOf(UpsertCategoryBase::class, $this->sut);
    }

    public function testItUpdatesTheCategoryWhenItAlreadyExists(): void
    {
        $this->getCategory->method('byCode')->with('socks')->willReturn($this->createMock(Category::class));
        $this->recordExecutedQueries();

        $this->sut->execute($this->buildCategory(attributes: $this->buildValueCollection()));

        $this->assertCount(1, $this->executedQueries);
        $query = $this->executedQueries[0];
        $this->assertStringContainsString('UPDATE pim_catalog_category', $query['sql']);
        $this->assertStringNotContainsString('INSERT INTO', $query['sql']);
        $this->assertSame(['category_code', 'value_collection'], array_keys($query['params']));
        $this->assertSame('socks', $query['params']['category_code']);
        $this->assertSame(
            [
                'category_code' => ParameterType::STRING,
                'value_collection' => Types::JSON,
            ],
            $query['types'],
        );
    }

    public function testItSkipsTheUpdateWhenTheCategoryTemplateIsDeactivated(): void
    {
        $this->getCategory->method('byCode')->willReturn($this->createMock(Category::class));
        $this->isTemplateDeactivated
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(true);
        $this->connection->expects($this->never())->method('executeQuery');

        $this->sut->execute($this->buildCategory(
            templateUuid: TemplateUuid::fromString(self::TEMPLATE_UUID),
            attributes: $this->buildValueCollection(),
        ));
    }

    public function testItUpdatesTheCategoryWhenItsTemplateIsActive(): void
    {
        $templateUuid = TemplateUuid::fromString(self::TEMPLATE_UUID);
        $this->getCategory->method('byCode')->willReturn($this->createMock(Category::class));
        $this->isTemplateDeactivated
            ->expects($this->once())
            ->method('__invoke')
            ->with($templateUuid)
            ->willReturn(false);
        $this->recordExecutedQueries();

        $this->sut->execute($this->buildCategory(
            templateUuid: $templateUuid,
            attributes: $this->buildValueCollection(),
        ));

        $this->assertCount(1, $this->executedQueries);
        $this->assertStringContainsString('UPDATE pim_catalog_category', $this->executedQueries[0]['sql']);
    }

    public function testItDoesNotCheckTheTemplateStatusWhenTheCategoryHasNoTemplate(): void
    {
        $this->getCategory->method('byCode')->willReturn($this->createMock(Category::class));
        $this->isTemplateDeactivated->expects($this->never())->method('__invoke');
        $this->recordExecutedQueries();

        $this->sut->execute($this->buildCategory(templateUuid: null));

        $this->assertCount(1, $this->executedQueries);
    }

    public function testItInsertsTheCategoryWhenItDoesNotExistYet(): void
    {
        $this->getCategory->method('byCode')->with('socks')->willReturn(null);
        $this->connection->expects($this->never())->method('lastInsertId');
        $this->recordExecutedQueries();

        $this->sut->execute($this->buildCategory(
            parentId: new CategoryId(3),
            rootId: new CategoryId(7),
            attributes: $this->buildValueCollection(),
        ));

        $this->assertCount(1, $this->executedQueries);
        $query = $this->executedQueries[0];
        $this->assertStringContainsString('INSERT INTO pim_catalog_category', $query['sql']);
        $this->assertSame(3, $query['params']['parent_id']);
        $this->assertSame('socks', $query['params']['code']);
        $this->assertSame(7, $query['params']['root']);
        $this->assertSame(0, $query['params']['lvl']);
        $this->assertSame(1, $query['params']['lft']);
        $this->assertSame(2, $query['params']['rgt']);
        $this->assertSame(
            [
                'parent_id' => ParameterType::INTEGER,
                'code' => ParameterType::STRING,
                'root' => ParameterType::INTEGER,
                'lvl' => ParameterType::INTEGER,
                'lft' => ParameterType::INTEGER,
                'rgt' => ParameterType::INTEGER,
                'value_collection' => Types::JSON,
            ],
            $query['types'],
        );
    }

    public function testItInsertsARootCategoryWithRootZeroThenBackfillsRootWithTheNewId(): void
    {
        $this->getCategory->method('byCode')->willReturn(null);
        $this->connection->expects($this->once())->method('lastInsertId')->willReturn('42');
        $this->recordExecutedQueries();

        $this->sut->execute($this->buildCategory(parentId: null, rootId: null));

        $this->assertCount(2, $this->executedQueries);

        $insert = $this->executedQueries[0];
        $this->assertStringContainsString('INSERT INTO pim_catalog_category', $insert['sql']);
        $this->assertNull($insert['params']['parent_id']);
        $this->assertSame(0, $insert['params']['root']);

        $backfill = $this->executedQueries[1];
        $this->assertStringContainsString('UPDATE pim_catalog_category', $backfill['sql']);
        $this->assertStringContainsString('SET root=:root', $backfill['sql']);
        $this->assertSame(
            [
                'category_code' => 'socks',
                'root' => '42',
            ],
            $backfill['params'],
        );
        $this->assertSame(
            [
                'category_code' => ParameterType::STRING,
                'root' => ParameterType::INTEGER,
            ],
            $backfill['types'],
        );
    }

    public function testItRefusesToInsertACategoryHavingAParentButNoRoot(): void
    {
        $this->getCategory->method('byCode')->willReturn(null);
        $this->connection->expects($this->never())->method('executeQuery');
        $category = $this->buildCategory(parentId: new CategoryId(3), rootId: null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a value other than null.');

        $this->sut->execute($category);
    }

    public function testItFiltersOutTheAttributeValuesWithoutData(): void
    {
        $this->getCategory->method('byCode')->willReturn($this->createMock(Category::class));
        $this->recordExecutedQueries();

        $this->sut->execute($this->buildCategory(attributes: $this->buildValueCollection()));

        $valueCollection = $this->executedQueries[0]['params']['value_collection'];
        $this->assertSame(['title|' . self::TITLE_UUID], array_keys($valueCollection));
        $this->assertSame('A title', $valueCollection['title|' . self::TITLE_UUID]['data']);
    }

    public function testItPassesANullValueCollectionWhenTheCategoryHasNoAttributes(): void
    {
        $this->getCategory->method('byCode')->willReturn($this->createMock(Category::class));
        $this->recordExecutedQueries();

        $this->sut->execute($this->buildCategory(attributes: null));

        $this->assertNull($this->executedQueries[0]['params']['value_collection']);
    }

    private function recordExecutedQueries(): void
    {
        $this->connection->method('executeQuery')->willReturnCallback(
            function (string $sql, array $params = [], array $types = []): Result {
                $this->executedQueries[] = ['sql' => $sql, 'params' => $params, 'types' => $types];

                return $this->createMock(Result::class);
            },
        );
    }

    private function buildCategory(
        ?TemplateUuid $templateUuid = null,
        ?CategoryId $parentId = null,
        ?CategoryId $rootId = null,
        ?ValueCollection $attributes = null,
    ): Category {
        return new Category(
            id: new CategoryId(12),
            code: new Code('socks'),
            templateUuid: $templateUuid,
            parentId: $parentId,
            rootId: $rootId,
            attributes: $attributes,
        );
    }

    /**
     * Builds a collection holding one value with data and one value without data.
     */
    private function buildValueCollection(): ValueCollection
    {
        return ValueCollection::fromDatabase([
            'title|' . self::TITLE_UUID => [
                'data' => 'A title',
                'type' => 'text',
                'channel' => null,
                'locale' => null,
                'attribute_code' => 'title|' . self::TITLE_UUID,
            ],
            'subtitle|' . self::SUBTITLE_UUID => [
                'data' => null,
                'type' => 'text',
                'channel' => null,
                'locale' => null,
                'attribute_code' => 'subtitle|' . self::SUBTITLE_UUID,
            ],
        ]);
    }
}
