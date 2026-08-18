<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAllConnectedAppsPublicIdsInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\GetAllConnectedAppsPublicIdsQuery;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllConnectedAppsPublicIdsQueryTest extends TestCase
{
    private DbalConnection|MockObject $dbalConnection;
    private GetAllConnectedAppsPublicIdsQuery $sut;

    protected function setUp(): void
    {
        $this->dbalConnection = $this->createMock(DbalConnection::class);
        $this->sut = new GetAllConnectedAppsPublicIdsQuery($this->dbalConnection);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetAllConnectedAppsPublicIdsQuery::class, $this->sut);
        $this->assertInstanceOf(GetAllConnectedAppsPublicIdsInterface::class, $this->sut);
    }

    public function test_it_returns_the_public_ids_of_the_connected_apps(): void
    {
        $this->dbalConnection
            ->expects($this->once())
            ->method('fetchFirstColumn')
            ->willReturn([
                '6ff52991-1a3b-4d4a-b4c4-a0e1cd1a4ad9',
                '90741597-54c5-48a1-98da-a68e7ee0a715',
            ]);

        $this->assertSame([
            '6ff52991-1a3b-4d4a-b4c4-a0e1cd1a4ad9',
            '90741597-54c5-48a1-98da-a68e7ee0a715',
        ], $this->sut->execute());
    }

    public function test_it_returns_an_empty_list_when_no_app_is_connected_yet(): void
    {
        $this->dbalConnection->method('fetchFirstColumn')->willReturn([]);

        $this->assertSame([], $this->sut->execute());
    }

    public function test_it_reads_the_public_id_from_the_api_client_joined_to_the_connected_apps(): void
    {
        $this->dbalConnection
            ->expects($this->once())
            ->method('fetchFirstColumn')
            ->willReturnCallback(function (string $sql): array {
                $this->assertMatchesRegularExpression(
                    '/SELECT\s+pim_api_client\.marketplace_public_app_id/i',
                    $sql,
                );
                $this->assertStringContainsString('akeneo_connectivity_connected_app', $sql);
                $this->assertStringContainsString('akeneo_connectivity_connection', $sql);
                $this->assertStringContainsString('pim_api_client', $sql);

                return [];
            });

        $this->sut->execute();
    }
}
