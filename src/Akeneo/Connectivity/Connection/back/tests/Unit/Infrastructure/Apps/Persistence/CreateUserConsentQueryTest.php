<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\CreateUserConsentQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\CreateUserConsentQuery;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateUserConsentQueryTest extends TestCase
{
    private DbalConnection|MockObject $connection;
    private SqlPlatformHelperInterface|MockObject $platformHelper;
    private CreateUserConsentQuery $sut;

    /** @var array<int, array{sql: string, params: array<string, mixed>, types: array<string, string>}> */
    private array $executedQueries = [];

    protected function setUp(): void
    {
        $this->connection = $this->createMock(DbalConnection::class);
        $this->platformHelper = $this->createMock(SqlPlatformHelperInterface::class);
        $this->sut = new CreateUserConsentQuery($this->connection, $this->platformHelper);

        $this->executedQueries = [];
        $result = $this->createMock(Result::class);
        $this->connection->method('executeQuery')->willReturnCallback(
            function (string $sql, array $params, array $types) use ($result): Result {
                $this->executedQueries[] = ['sql' => $sql, 'params' => $params, 'types' => $types];

                return $result;
            }
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CreateUserConsentQuery::class, $this->sut);
        $this->assertInstanceOf(CreateUserConsentQueryInterface::class, $this->sut);
    }

    public function test_it_inserts_the_consent_of_the_user_for_the_app(): void
    {
        $this->platformHelper->method('upsertClause')->willReturn('ON CONFLICT DO NOTHING');
        $consentDate = new \DateTimeImmutable('2026-07-29 10:11:12', new \DateTimeZone('UTC'));

        $this->sut->execute(42, 'an_app_id', ['email', 'profile'], $consentDate);

        $this->assertCount(1, $this->executedQueries);
        [$executedQuery] = $this->executedQueries;

        $this->assertStringContainsString(
            'INSERT INTO akeneo_connectivity_user_consent (user_id, app_id, scopes, uuid, consent_date)',
            $executedQuery['sql'],
        );
        $this->assertSame(42, $executedQuery['params']['userId']);
        $this->assertSame('an_app_id', $executedQuery['params']['appId']);
        $this->assertSame(['email', 'profile'], $executedQuery['params']['scopes']);
        $this->assertSame($consentDate, $executedQuery['params']['consentDate']);
        $this->assertSame([
            'userId' => Types::INTEGER,
            'appId' => Types::STRING,
            'scopes' => Types::JSON,
            'uuid' => Types::ASCII_STRING,
            'consentDate' => Types::DATETIMETZ_IMMUTABLE,
        ], $executedQuery['types']);
    }

    public function test_it_reindexes_the_scopes_so_that_they_are_stored_as_a_json_array(): void
    {
        $this->platformHelper->method('upsertClause')->willReturn('ON CONFLICT DO NOTHING');

        $this->sut->execute(42, 'an_app_id', [2 => 'profile', 5 => 'email'], new \DateTimeImmutable());

        $this->assertSame(['profile', 'email'], $this->executedQueries[0]['params']['scopes']);
        $this->assertSame([0, 1], \array_keys($this->executedQueries[0]['params']['scopes']));
    }

    public function test_it_appends_the_platform_upsert_clause_so_that_re_consenting_updates_the_row(): void
    {
        $this->platformHelper
            ->expects($this->once())
            ->method('upsertClause')
            ->with(['user_id', 'app_id'], ['scopes = :scopes', 'consent_date = :consentDate'])
            ->willReturn('ON DUPLICATE KEY UPDATE scopes = :scopes, consent_date = :consentDate');

        $this->sut->execute(42, 'an_app_id', ['email'], new \DateTimeImmutable());

        $this->assertStringContainsString(
            'ON DUPLICATE KEY UPDATE scopes = :scopes, consent_date = :consentDate',
            $this->executedQueries[0]['sql'],
        );
    }

    public function test_it_generates_a_distinct_uuid_for_each_consent(): void
    {
        $this->platformHelper->method('upsertClause')->willReturn('ON CONFLICT DO NOTHING');

        $this->sut->execute(42, 'an_app_id', ['email'], new \DateTimeImmutable());
        $this->sut->execute(42, 'another_app_id', ['email'], new \DateTimeImmutable());

        $this->assertCount(2, $this->executedQueries);
        $this->assertInstanceOf(UuidInterface::class, $this->executedQueries[0]['params']['uuid']);
        $this->assertInstanceOf(UuidInterface::class, $this->executedQueries[1]['params']['uuid']);
        $this->assertNotSame(
            $this->executedQueries[0]['params']['uuid']->toString(),
            $this->executedQueries[1]['params']['uuid']->toString(),
        );
    }
}
