<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\CreateConnectedAppQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\CreateConnectedAppQuery;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateConnectedAppQueryTest extends TestCase
{
    private DbalConnection|MockObject $dbalConnection;
    private CreateConnectedAppQuery $sut;

    protected function setUp(): void
    {
        $this->dbalConnection = $this->createMock(DbalConnection::class);
        $this->sut = new CreateConnectedAppQuery($this->dbalConnection);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CreateConnectedAppQuery::class, $this->sut);
        $this->assertInstanceOf(CreateConnectedAppQueryInterface::class, $this->sut);
    }

    public function test_it_inserts_the_connected_app_row(): void
    {
        $sql = null;
        $params = null;
        $types = null;

        $this->dbalConnection
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturnCallback(
                function (string $executedSql, array $executedParams, array $executedTypes) use (&$sql, &$params, &$types): Result {
                    $sql = $executedSql;
                    $params = $executedParams;
                    $types = $executedTypes;

                    return $this->createMock(Result::class);
                }
            );

        $this->sut->execute(new ConnectedApp(
            '6ff52991-0d5e-4dd0-91f1-fc4d9d0e5f9e',
            'App prototype',
            ['read_products', 'write_products', 'delete_products'],
            'a_connection_code',
            'https://marketplace.test/logo.png',
            'Akeneo',
            'app_a_user_group_name',
            'a_connection_username',
            ['E-commerce', 'Print'],
            true,
            'Akeneo Partner',
        ));

        $this->assertIsString($sql);
        $this->assertStringContainsString('INSERT INTO akeneo_connectivity_connected_app', $sql);

        $this->assertSame([
            'id' => '6ff52991-0d5e-4dd0-91f1-fc4d9d0e5f9e',
            'name' => 'App prototype',
            'logo' => 'https://marketplace.test/logo.png',
            'author' => 'Akeneo',
            'partner' => 'Akeneo Partner',
            'categories' => ['E-commerce', 'Print'],
            'scopes' => ['read_products', 'write_products', 'delete_products'],
            'certified' => true,
            'connection_code' => 'a_connection_code',
            'user_group_name' => 'app_a_user_group_name',
        ], $params);
    }

    public function test_it_declares_the_json_and_boolean_dbal_types_of_the_non_scalar_columns(): void
    {
        $types = null;

        $this->dbalConnection
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturnCallback(
                function (string $executedSql, array $executedParams, array $executedTypes) use (&$types): Result {
                    $types = $executedTypes;

                    return $this->createMock(Result::class);
                }
            );

        $this->sut->execute($this->aConnectedApp());

        $this->assertSame([
            'certified' => Types::BOOLEAN,
            'categories' => Types::JSON,
            'scopes' => Types::JSON,
        ], $types);
    }

    public function test_it_inserts_a_connected_app_without_logo_author_or_partner(): void
    {
        $params = null;

        $this->dbalConnection
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturnCallback(
                function (string $executedSql, array $executedParams, array $executedTypes) use (&$params): Result {
                    $params = $executedParams;

                    return $this->createMock(Result::class);
                }
            );

        $this->sut->execute(new ConnectedApp(
            'a_custom_app_id',
            'A custom app',
            [],
            'another_connection_code',
            null,
            null,
            'app_another_user_group_name',
            'another_connection_username',
        ));

        $this->assertNull($params['logo']);
        $this->assertNull($params['author']);
        $this->assertNull($params['partner']);
        $this->assertSame([], $params['categories']);
        $this->assertSame([], $params['scopes']);
        $this->assertFalse($params['certified']);
    }

    private function aConnectedApp(): ConnectedApp
    {
        return new ConnectedApp(
            '6ff52991-0d5e-4dd0-91f1-fc4d9d0e5f9e',
            'App prototype',
            ['read_products'],
            'a_connection_code',
            'https://marketplace.test/logo.png',
            'Akeneo',
            'app_a_user_group_name',
            'a_connection_username',
            ['E-commerce'],
            true,
            'Akeneo Partner',
        );
    }
}
