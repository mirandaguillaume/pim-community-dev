<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAllPendingAppsPublicIdsQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\GetAllPendingAppsPublicIdsQuery;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllPendingAppsPublicIdsQueryTest extends TestCase
{
    private DbalConnection|MockObject $dbalConnection;
    private GetAllPendingAppsPublicIdsQuery $sut;

    protected function setUp(): void
    {
        $this->dbalConnection = $this->createMock(DbalConnection::class);
        $this->sut = new GetAllPendingAppsPublicIdsQuery($this->dbalConnection);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetAllPendingAppsPublicIdsQuery::class, $this->sut);
        $this->assertInstanceOf(GetAllPendingAppsPublicIdsQueryInterface::class, $this->sut);
    }

    public function test_it_returns_the_public_ids_of_the_pending_apps(): void
    {
        $this->dbalConnection
            ->expects($this->once())
            ->method('fetchFirstColumn')
            ->willReturn(['6ff52991-1a3b-4d4a-b4c4-a0e1cd1a4ad9']);

        $this->assertSame(['6ff52991-1a3b-4d4a-b4c4-a0e1cd1a4ad9'], $this->sut->execute());
    }

    public function test_it_returns_an_empty_list_when_no_app_is_pending(): void
    {
        $this->dbalConnection->method('fetchFirstColumn')->willReturn([]);

        $this->assertSame([], $this->sut->execute());
    }

    public function test_it_selects_the_clients_having_an_auth_code_but_no_access_token(): void
    {
        $this->dbalConnection
            ->expects($this->once())
            ->method('fetchFirstColumn')
            ->willReturnCallback(function (string $sql): array {
                $this->assertMatchesRegularExpression('/SELECT\s+marketplace_public_app_id/i', $sql);
                $this->assertStringContainsString('pim_api_client', $sql);
                $this->assertStringContainsString('pim_api_access_token', $sql);
                $this->assertStringContainsString('pim_api_auth_code', $sql);
                $this->assertMatchesRegularExpression(
                    '/WHERE\s+access_token\.token\s+IS\s+NULL\s+AND\s+auth_code\.token\s+IS\s+NOT\s+NULL/i',
                    $sql,
                );

                return [];
            });

        $this->sut->execute();
    }
}
