<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetConnectedAppRoleIdentifierQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\GetConnectedAppRoleIdentifierQuery;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetConnectedAppRoleIdentifierQueryTest extends TestCase
{
    private const string APP_ID = '6ff52991-0d5e-4dd0-91f1-fc4d9d0e5f9e';

    private DbalConnection|MockObject $dbalConnection;
    private GetConnectedAppRoleIdentifierQuery $sut;

    private ?string $sql = null;
    private ?array $params = null;

    protected function setUp(): void
    {
        $this->dbalConnection = $this->createMock(DbalConnection::class);
        $this->sut = new GetConnectedAppRoleIdentifierQuery($this->dbalConnection);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetConnectedAppRoleIdentifierQuery::class, $this->sut);
        $this->assertInstanceOf(GetConnectedAppRoleIdentifierQueryInterface::class, $this->sut);
    }

    public function test_it_returns_the_role_identifier_of_the_connected_app(): void
    {
        $this->givenTheDatabaseReturns('ROLE_APP_PROTOTYPE');

        $this->assertSame('ROLE_APP_PROTOTYPE', $this->sut->execute(self::APP_ID));
        $this->assertSame(['app_id' => self::APP_ID], $this->params);
    }

    public function test_it_looks_up_the_role_through_the_connection_user(): void
    {
        $this->givenTheDatabaseReturns('ROLE_APP_PROTOTYPE');

        $this->sut->execute(self::APP_ID);

        $this->assertStringContainsString('SELECT role.role', $this->sql);
        $this->assertStringContainsString('akeneo_connectivity_connected_app', $this->sql);
        $this->assertStringContainsString('akeneo_connectivity_connection', $this->sql);
        $this->assertStringContainsString('oro_user_access_role', $this->sql);
        $this->assertStringContainsString('oro_access_role', $this->sql);
        $this->assertStringContainsString('WHERE app.id = :app_id', $this->sql);
    }

    public function test_it_returns_null_when_the_app_has_no_role(): void
    {
        $this->givenTheDatabaseReturns(false);

        $this->assertNull($this->sut->execute(self::APP_ID));
    }

    public function test_it_returns_null_when_the_role_identifier_is_empty(): void
    {
        $this->givenTheDatabaseReturns('');

        $this->assertNull($this->sut->execute(self::APP_ID));
    }

    private function givenTheDatabaseReturns(string|false $roleIdentifier): void
    {
        $this->dbalConnection
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturnCallback(
                function (string $sql, array $params) use ($roleIdentifier): string|false {
                    $this->sql = $sql;
                    $this->params = $params;

                    return $roleIdentifier;
                }
            );
    }
}
