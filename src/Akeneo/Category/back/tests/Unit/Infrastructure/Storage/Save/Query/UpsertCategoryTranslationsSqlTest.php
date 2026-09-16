<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Storage\Save\Query;

use Akeneo\Category\Application\Storage\Save\Query\UpsertCategoryTranslations;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Infrastructure\Storage\Save\Query\UpsertCategoryTranslationsSql;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpsertCategoryTranslationsSqlTest extends TestCase
{
    private Connection|MockObject $connection;
    private GetCategoryInterface|MockObject $getCategory;
    private SqlPlatformHelperInterface|MockObject $platformHelper;
    private UpsertCategoryTranslationsSql $sut;

    /**
     * @var array<int, array{sql: string, params: array<string, mixed>, types: array<string, mixed>}>
     */
    private array $executedQueries = [];

    /**
     * @var array<int, array{conflictColumns: array<string>, updateExpressions: array<string>}>
     */
    private array $upsertClauseCalls = [];

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->getCategory = $this->createMock(GetCategoryInterface::class);
        $this->platformHelper = $this->createMock(SqlPlatformHelperInterface::class);
        $this->executedQueries = [];
        $this->upsertClauseCalls = [];

        $this->platformHelper->method('upsertClause')->willReturnCallback(
            function (array $conflictColumns, array $updateExpressions): string {
                $this->upsertClauseCalls[] = [
                    'conflictColumns' => $conflictColumns,
                    'updateExpressions' => $updateExpressions,
                ];

                return 'ON DUPLICATE KEY UPDATE ' . implode(', ', $updateExpressions);
            },
        );

        $this->sut = new UpsertCategoryTranslationsSql(
            $this->connection,
            $this->getCategory,
            $this->platformHelper,
        );
    }

    public function testItIsAnUpsertCategoryTranslations(): void
    {
        $this->assertInstanceOf(UpsertCategoryTranslations::class, $this->sut);
    }

    public function testItThrowsWhenTheCategoryIdIsNull(): void
    {
        $this->connection->expects($this->never())->method('executeQuery');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot upsert category translations on null id.');

        $this->sut->execute($this->buildCategory(null, ['en_US' => 'Socks']));
    }

    public function testItUpsertsOneStatementPerLabelWhenTheCategoryIsUnknown(): void
    {
        $this->getCategory->method('byCode')->with('socks')->willReturn(null);
        $this->recordExecutedQueries();

        $this->sut->execute($this->buildCategory(12, ['en_US' => 'Socks', 'fr_FR' => 'Chaussettes']));

        $this->assertCount(1, $this->executedQueries);
        $query = $this->executedQueries[0];
        $this->assertSame(
            2,
            substr_count($query['sql'], 'INSERT INTO pim_catalog_category_translation'),
        );
        $this->assertSame(
            [
                'category_id' => 12,
                'label0' => 'Socks',
                'locale0' => 'en_US',
                'label1' => 'Chaussettes',
                'locale1' => 'fr_FR',
            ],
            $query['params'],
        );
        $this->assertSame(
            [
                'category_id' => ParameterType::INTEGER,
                'label0' => ParameterType::STRING,
                'locale0' => ParameterType::STRING,
                'label1' => ParameterType::STRING,
                'locale1' => ParameterType::STRING,
            ],
            $query['types'],
        );
    }

    public function testItDelegatesTheUpsertClauseToThePlatformHelperForEachLabel(): void
    {
        $this->getCategory->method('byCode')->willReturn(null);
        $this->recordExecutedQueries();

        $this->sut->execute($this->buildCategory(12, ['en_US' => 'Socks', 'fr_FR' => 'Chaussettes']));

        $this->assertSame(
            [
                ['conflictColumns' => ['foreign_key', 'locale'], 'updateExpressions' => ['label = :label0']],
                ['conflictColumns' => ['foreign_key', 'locale'], 'updateExpressions' => ['label = :label1']],
            ],
            $this->upsertClauseCalls,
        );
        $this->assertStringContainsString(
            'ON DUPLICATE KEY UPDATE label = :label0',
            $this->executedQueries[0]['sql'],
        );
        $this->assertStringContainsString(
            'ON DUPLICATE KEY UPDATE label = :label1',
            $this->executedQueries[0]['sql'],
        );
    }

    public function testItSkipsTheLabelsIdenticalToTheStoredOnes(): void
    {
        $this->getCategory->method('byCode')->willReturn(
            $this->buildCategory(12, ['en_US' => 'Socks']),
        );
        $this->recordExecutedQueries();

        $this->sut->execute($this->buildCategory(12, ['en_US' => 'Socks', 'fr_FR' => 'Chaussettes']));

        $this->assertCount(1, $this->executedQueries);
        $query = $this->executedQueries[0];
        $this->assertSame(
            1,
            substr_count($query['sql'], 'INSERT INTO pim_catalog_category_translation'),
        );
        $this->assertSame(
            [
                'category_id' => 12,
                'label0' => 'Chaussettes',
                'locale0' => 'fr_FR',
            ],
            $query['params'],
        );
    }

    public function testItDoesNotExecuteAnyQueryWhenEveryLabelIsUnchanged(): void
    {
        $this->getCategory->method('byCode')->willReturn(
            $this->buildCategory(12, ['en_US' => 'Socks', 'fr_FR' => 'Chaussettes']),
        );
        $this->connection->expects($this->never())->method('executeQuery');

        $this->sut->execute($this->buildCategory(12, ['en_US' => 'Socks', 'fr_FR' => 'Chaussettes']));

        $this->assertSame([], $this->upsertClauseCalls);
    }

    public function testItUpsertsALabelWhoseValueChanged(): void
    {
        $this->getCategory->method('byCode')->willReturn(
            $this->buildCategory(12, ['en_US' => 'Old socks']),
        );
        $this->recordExecutedQueries();

        $this->sut->execute($this->buildCategory(12, ['en_US' => 'Socks']));

        $this->assertCount(1, $this->executedQueries);
        $this->assertSame(
            [
                'category_id' => 12,
                'label0' => 'Socks',
                'locale0' => 'en_US',
            ],
            $this->executedQueries[0]['params'],
        );
    }

    public function testItUpsertsANullLabelWhenTheStoredLabelIsRemoved(): void
    {
        $this->getCategory->method('byCode')->willReturn(
            $this->buildCategory(12, ['en_US' => 'Socks']),
        );
        $this->recordExecutedQueries();

        $this->sut->execute($this->buildCategory(12, ['en_US' => null]));

        $this->assertCount(1, $this->executedQueries);
        $this->assertSame(
            [
                'category_id' => 12,
                'label0' => null,
                'locale0' => 'en_US',
            ],
            $this->executedQueries[0]['params'],
        );
        $this->assertSame(ParameterType::STRING, $this->executedQueries[0]['types']['label0']);
    }

    public function testItUpsertsEveryLabelWhenTheStoredCategoryHasNoTranslation(): void
    {
        $this->getCategory->method('byCode')->willReturn(
            new Category(id: new CategoryId(12), code: new Code('socks'), templateUuid: null, labels: null),
        );
        $this->recordExecutedQueries();

        $this->sut->execute($this->buildCategory(12, ['en_US' => 'Socks']));

        $this->assertCount(1, $this->executedQueries);
        $this->assertSame(
            [
                'category_id' => 12,
                'label0' => 'Socks',
                'locale0' => 'en_US',
            ],
            $this->executedQueries[0]['params'],
        );
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

    /**
     * @param array<string, string|null> $labels
     */
    private function buildCategory(?int $id, array $labels): Category
    {
        return new Category(
            id: null === $id ? null : new CategoryId($id),
            code: new Code('socks'),
            templateUuid: null,
            labels: LabelCollection::fromArray($labels),
        );
    }
}
