<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\UpdateConnectedAppScopesQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\UpdateConnectedAppScopesQuery;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateConnectedAppScopesQueryTest extends TestCase
{
    private const string APP_ID = '6ff52991-0d5e-4dd0-91f1-fc4d9d0e5f9e';

    private DbalConnection|MockObject $dbalConnection;
    private UpdateConnectedAppScopesQuery $sut;

    private ?string $sql = null;
    private ?array $params = null;
    private ?array $types = null;

    protected function setUp(): void
    {
        $this->dbalConnection = $this->createMock(DbalConnection::class);
        $this->sut = new UpdateConnectedAppScopesQuery($this->dbalConnection);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(UpdateConnectedAppScopesQuery::class, $this->sut);
        $this->assertInstanceOf(UpdateConnectedAppScopesQueryInterface::class, $this->sut);
    }

    public function test_it_stores_the_new_scopes_of_the_connected_app_as_json(): void
    {
        $this->captureTheExecutedQuery();

        $this->sut->execute(['read_products', 'write_products', 'delete_products'], self::APP_ID);

        $this->assertSame(
            [
                'scopes' => ['read_products', 'write_products', 'delete_products'],
                'id' => self::APP_ID,
            ],
            $this->params
        );
        $this->assertSame(['scopes' => Types::JSON], $this->types);
    }

    public function test_it_updates_only_the_scopes_and_the_update_date_of_the_targeted_app(): void
    {
        $this->captureTheExecutedQuery();

        $this->sut->execute(['read_products'], self::APP_ID);

        $this->assertStringContainsString('UPDATE akeneo_connectivity_connected_app', $this->sql);
        $this->assertStringContainsString('scopes = :scopes', $this->sql);
        $this->assertStringContainsString('updated = NOW()', $this->sql);
        $this->assertStringContainsString('WHERE id = :id', $this->sql);
    }

    public function test_it_revokes_every_scope_when_an_empty_scope_list_is_given(): void
    {
        $this->captureTheExecutedQuery();

        $this->sut->execute([], self::APP_ID);

        $this->assertSame([], $this->params['scopes']);
    }

    private function captureTheExecutedQuery(): void
    {
        $this->dbalConnection
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturnCallback(
                function (string $sql, array $params, array $types): Result {
                    $this->sql = $sql;
                    $this->params = $params;
                    $this->types = $types;

                    return $this->createMock(Result::class);
                }
            );
    }
}
