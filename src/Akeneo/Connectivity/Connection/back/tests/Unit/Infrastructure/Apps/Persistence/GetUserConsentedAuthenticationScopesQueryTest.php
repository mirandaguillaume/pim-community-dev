<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetUserConsentedAuthenticationScopesQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\GetUserConsentedAuthenticationScopesQuery;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUserConsentedAuthenticationScopesQueryTest extends TestCase
{
    private DbalConnection|MockObject $connection;
    private GetUserConsentedAuthenticationScopesQuery $sut;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(DbalConnection::class);
        $this->sut = new GetUserConsentedAuthenticationScopesQuery($this->connection);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetUserConsentedAuthenticationScopesQuery::class, $this->sut);
        $this->assertInstanceOf(GetUserConsentedAuthenticationScopesQueryInterface::class, $this->sut);
    }

    public function test_it_returns_the_authentication_scopes_the_user_consented_to(): void
    {
        $this->connection
            ->expects($this->once())
            ->method('fetchOne')
            ->with(
                $this->stringContains('akeneo_connectivity_user_consent'),
                ['userId' => 42, 'appId' => 'an_app_id'],
            )
            ->willReturn('["email","profile"]');

        $this->assertSame(['email', 'profile'], $this->sut->execute(42, 'an_app_id'));
    }

    public function test_it_returns_an_empty_array_when_the_user_has_no_consent_row(): void
    {
        $this->connection->expects($this->once())->method('fetchOne')->willReturn(false);

        $this->assertSame([], $this->sut->execute(42, 'an_app_id'));
    }

    public function test_it_returns_an_empty_array_when_the_stored_scopes_are_an_empty_json_array(): void
    {
        $this->connection->expects($this->once())->method('fetchOne')->willReturn('[]');

        $this->assertSame([], $this->sut->execute(42, 'an_app_id'));
    }

    public function test_it_throws_when_the_stored_scopes_are_not_valid_json(): void
    {
        $this->connection->expects($this->once())->method('fetchOne')->willReturn('not a json payload');

        $this->expectException(\JsonException::class);
        $this->sut->execute(42, 'an_app_id');
    }

    public function test_it_reads_the_scopes_of_the_given_user_and_app_only(): void
    {
        $executedSql = null;
        $this->connection
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturnCallback(function (string $sql) use (&$executedSql): string {
                $executedSql = $sql;

                return '[]';
            });

        $this->sut->execute(42, 'an_app_id');

        $this->assertNotNull($executedSql);
        $this->assertStringContainsString('SELECT scopes FROM akeneo_connectivity_user_consent', $executedSql);
        $this->assertStringContainsString('WHERE user_id = :userId AND app_id = :appId', $executedSql);
    }
}
