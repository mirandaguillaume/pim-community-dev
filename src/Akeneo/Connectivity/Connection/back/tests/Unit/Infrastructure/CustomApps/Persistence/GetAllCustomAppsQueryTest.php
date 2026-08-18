<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Domain\CustomApps\DTO\GetAllCustomAppsResult;
use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetAllCustomAppsQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\GetAllCustomAppsQuery;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllCustomAppsQueryTest extends TestCase
{
    private DbalConnection|MockObject $dbalConnection;
    private SqlPlatformHelperInterface|MockObject $platformHelper;
    private GetAllCustomAppsQuery $sut;

    protected function setUp(): void
    {
        $this->dbalConnection = $this->createMock(DbalConnection::class);
        $this->platformHelper = $this->createMock(SqlPlatformHelperInterface::class);
        $this->platformHelper->method('conditional')->willReturn('THE_AUTHOR_EXPRESSION');
        $this->sut = new GetAllCustomAppsQuery($this->dbalConnection, $this->platformHelper);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetAllCustomAppsQuery::class, $this->sut);
        $this->assertInstanceOf(GetAllCustomAppsQueryInterface::class, $this->sut);
    }

    public function test_it_returns_the_custom_apps_found_in_database(): void
    {
        $this->dbalConnection->method('fetchAllAssociative')->willReturn([
            [
                'id' => '6ff52991-1a3b-4d4a-b4c4-a0e1cd1a4ad9',
                'name' => 'App prototype',
                'author' => 'Julia Stark',
                'activate_url' => 'https://custom-app.example.com/activate',
                'callback_url' => 'https://custom-app.example.com/callback',
                'connected' => '0',
            ],
        ]);

        $result = $this->sut->execute();

        $this->assertInstanceOf(GetAllCustomAppsResult::class, $result);
        $normalized = $result->normalize();

        $this->assertSame(1, $normalized['total']);
        $this->assertCount(1, $normalized['apps']);
        $this->assertSame('6ff52991-1a3b-4d4a-b4c4-a0e1cd1a4ad9', $normalized['apps'][0]['id']);
        $this->assertSame('App prototype', $normalized['apps'][0]['name']);
        $this->assertSame('Julia Stark', $normalized['apps'][0]['author']);
        $this->assertSame('https://custom-app.example.com/activate', $normalized['apps'][0]['activate_url']);
        $this->assertSame('https://custom-app.example.com/callback', $normalized['apps'][0]['callback_url']);
        $this->assertTrue($normalized['apps'][0]['isCustomApp']);
    }

    public function test_it_casts_the_connected_count_of_zero_to_false(): void
    {
        $this->dbalConnection->method('fetchAllAssociative')->willReturn([$this->row('0')]);

        $normalized = $this->sut->execute()->normalize();

        $this->assertFalse($normalized['apps'][0]['connected']);
    }

    public function test_it_casts_a_non_zero_connected_count_to_true(): void
    {
        $this->dbalConnection->method('fetchAllAssociative')->willReturn([$this->row('1')]);

        $normalized = $this->sut->execute()->normalize();

        $this->assertTrue($normalized['apps'][0]['connected']);
    }

    public function test_it_returns_a_null_author_when_the_custom_app_has_no_user(): void
    {
        $row = $this->row('0');
        $row['author'] = null;
        $this->dbalConnection->method('fetchAllAssociative')->willReturn([$row]);

        $normalized = $this->sut->execute()->normalize();

        $this->assertNull($normalized['apps'][0]['author']);
    }

    public function test_it_returns_an_empty_result_when_no_custom_app_exists(): void
    {
        $this->dbalConnection->method('fetchAllAssociative')->willReturn([]);

        $normalized = $this->sut->execute()->normalize();

        $this->assertSame(0, $normalized['total']);
        $this->assertSame([], $normalized['apps']);
    }

    public function test_it_totals_every_returned_row(): void
    {
        $this->dbalConnection->method('fetchAllAssociative')->willReturn([
            $this->row('0', 'first_id'),
            $this->row('0', 'second_id'),
            $this->row('1', 'third_id'),
        ]);

        $normalized = $this->sut->execute()->normalize();

        $this->assertSame(3, $normalized['total']);
        $this->assertCount(3, $normalized['apps']);
    }

    public function test_it_selects_the_custom_apps_with_their_connected_status(): void
    {
        $this->dbalConnection
            ->expects($this->once())
            ->method('fetchAllAssociative')
            ->willReturnCallback(function (string $sql): array {
                $this->assertStringContainsString('akeneo_connectivity_test_app', $sql);
                $this->assertStringContainsString('akeneo_connectivity_connected_app', $sql);
                $this->assertMatchesRegularExpression('/COUNT\(connected_app\.id\)/i', $sql);
                $this->assertMatchesRegularExpression('/AS\s+connected/i', $sql);
                $this->assertStringContainsString('oro_user', $sql);

                return [];
            });

        $this->sut->execute();
    }

    public function test_it_builds_the_author_column_with_the_platform_helper(): void
    {
        $platformHelper = $this->createMock(SqlPlatformHelperInterface::class);
        $platformHelper
            ->expects($this->once())
            ->method('conditional')
            ->willReturnCallback(function (string $condition, string $then, string $else): string {
                $this->assertSame('app.user_id IS NOT NULL', $condition);
                $this->assertStringContainsString('CONCAT_WS', $then);
                $this->assertSame('NULL', $else);

                return 'THE_AUTHOR_EXPRESSION';
            });

        $this->dbalConnection
            ->method('fetchAllAssociative')
            ->willReturnCallback(function (string $sql): array {
                $this->assertMatchesRegularExpression('/THE_AUTHOR_EXPRESSION\s+AS\s+author/i', $sql);

                return [];
            });

        (new GetAllCustomAppsQuery($this->dbalConnection, $platformHelper))->execute();
    }

    /**
     * @return array<string, string|null>
     */
    private function row(string $connected, string $id = '6ff52991-1a3b-4d4a-b4c4-a0e1cd1a4ad9'): array
    {
        return [
            'id' => $id,
            'name' => 'App prototype',
            'author' => 'Julia Stark',
            'activate_url' => 'https://custom-app.example.com/activate',
            'callback_url' => 'https://custom-app.example.com/callback',
            'connected' => $connected,
        ];
    }
}
