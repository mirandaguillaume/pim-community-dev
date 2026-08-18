<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\GetAppConfirmationQuery;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAppConfirmationQueryTest extends TestCase
{
    private DbalConnection|MockObject $dbalConnection;
    private GetAppConfirmationQuery $sut;

    protected function setUp(): void
    {
        $this->dbalConnection = $this->createMock(DbalConnection::class);
        $this->sut = new GetAppConfirmationQuery($this->dbalConnection);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetAppConfirmationQuery::class, $this->sut);
        $this->assertInstanceOf(GetAppConfirmationQueryInterface::class, $this->sut);
    }

    public function test_it_returns_the_app_confirmation_of_a_connected_app(): void
    {
        $sql = null;
        $params = null;

        $this->dbalConnection
            ->expects($this->once())
            ->method('fetchAllAssociative')
            ->willReturnCallback(
                function (string $executedSql, array $executedParams) use (&$sql, &$params): array {
                    $sql = $executedSql;
                    $params = $executedParams;

                    return [
                        [
                            'app_id' => '6ff52991-0d5e-4dd0-91f1-fc4d9d0e5f9e',
                            'user_id' => '42',
                            'user_group' => 'app_a_user_group_name',
                            'fos_client_id' => '7',
                        ],
                    ];
                }
            );

        $appConfirmation = $this->sut->execute('6ff52991-0d5e-4dd0-91f1-fc4d9d0e5f9e');

        $this->assertSame(
            ['marketplace_public_app_id' => '6ff52991-0d5e-4dd0-91f1-fc4d9d0e5f9e'],
            $params
        );
        $this->assertIsString($sql);
        $this->assertStringContainsString('pim_api_client.marketplace_public_app_id = :marketplace_public_app_id', $sql);

        $this->assertInstanceOf(AppConfirmation::class, $appConfirmation);
        $this->assertSame('6ff52991-0d5e-4dd0-91f1-fc4d9d0e5f9e', $appConfirmation->getAppId());
        $this->assertSame(42, $appConfirmation->getUserId());
        $this->assertSame('app_a_user_group_name', $appConfirmation->getUserGroup());
        $this->assertSame(7, $appConfirmation->getFosClientId());
    }

    public function test_it_returns_null_when_the_app_has_not_been_connected_yet(): void
    {
        $this->dbalConnection
            ->expects($this->once())
            ->method('fetchAllAssociative')
            ->willReturn([]);

        $this->assertNull($this->sut->execute('an_unknown_marketplace_app_id'));
    }

    public function test_it_throws_a_logic_exception_when_several_connected_apps_share_the_same_marketplace_id(): void
    {
        $this->dbalConnection
            ->expects($this->once())
            ->method('fetchAllAssociative')
            ->willReturn([
                [
                    'app_id' => 'an_app_id',
                    'user_id' => '42',
                    'user_group' => 'app_a_user_group_name',
                    'fos_client_id' => '7',
                ],
                [
                    'app_id' => 'another_app_id',
                    'user_id' => '43',
                    'user_group' => 'app_another_user_group_name',
                    'fos_client_id' => '8',
                ],
            ]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('There should be only one connected app by marketplace id');

        $this->sut->execute('6ff52991-0d5e-4dd0-91f1-fc4d9d0e5f9e');
    }
}
