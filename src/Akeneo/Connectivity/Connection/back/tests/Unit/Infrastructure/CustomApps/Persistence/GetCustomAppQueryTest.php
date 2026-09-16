<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\GetCustomAppQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\GetCustomAppQuery;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCustomAppQueryTest extends TestCase
{
    private DbalConnection|MockObject $dbalConnection;
    private SqlPlatformHelperInterface|MockObject $platformHelper;
    private GetCustomAppQuery $sut;

    protected function setUp(): void
    {
        $this->dbalConnection = $this->createMock(DbalConnection::class);
        $this->platformHelper = $this->createMock(SqlPlatformHelperInterface::class);
        $this->platformHelper->method('conditional')->willReturn('THE_AUTHOR_EXPRESSION');
        $this->sut = new GetCustomAppQuery($this->dbalConnection, $this->platformHelper);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetCustomAppQuery::class, $this->sut);
        $this->assertInstanceOf(GetCustomAppQueryInterface::class, $this->sut);
    }

    public function test_it_returns_the_custom_app_matching_the_given_client_id(): void
    {
        $this->dbalConnection->method('fetchAssociative')->willReturn([
            'id' => '6ff52991-1a3b-4d4a-b4c4-a0e1cd1a4ad9',
            'name' => 'App prototype',
            'author' => 'Julia Stark',
            'activate_url' => 'https://custom-app.example.com/activate',
            'callback_url' => 'https://custom-app.example.com/callback',
            'connected' => '0',
        ]);

        $this->assertSame([
            'id' => '6ff52991-1a3b-4d4a-b4c4-a0e1cd1a4ad9',
            'name' => 'App prototype',
            'author' => 'Julia Stark',
            'activate_url' => 'https://custom-app.example.com/activate',
            'callback_url' => 'https://custom-app.example.com/callback',
            'connected' => false,
        ], $this->sut->execute('6ff52991-1a3b-4d4a-b4c4-a0e1cd1a4ad9'));
    }

    public function test_it_casts_a_non_zero_connected_count_to_true(): void
    {
        $this->dbalConnection->method('fetchAssociative')->willReturn($this->row('1'));

        $result = $this->sut->execute('6ff52991-1a3b-4d4a-b4c4-a0e1cd1a4ad9');

        $this->assertTrue($result['connected']);
    }

    public function test_it_casts_the_connected_count_of_zero_to_false(): void
    {
        $this->dbalConnection->method('fetchAssociative')->willReturn($this->row('0'));

        $result = $this->sut->execute('6ff52991-1a3b-4d4a-b4c4-a0e1cd1a4ad9');

        $this->assertFalse($result['connected']);
    }

    public function test_it_returns_null_when_no_custom_app_matches_the_given_client_id(): void
    {
        $this->dbalConnection->method('fetchAssociative')->willReturn(false);

        $this->assertNull($this->sut->execute('unknown_client_id'));
    }

    public function test_it_returns_a_null_author_when_the_custom_app_has_no_user(): void
    {
        $row = $this->row('0');
        $row['author'] = null;
        $this->dbalConnection->method('fetchAssociative')->willReturn($row);

        $result = $this->sut->execute('6ff52991-1a3b-4d4a-b4c4-a0e1cd1a4ad9');

        $this->assertNull($result['author']);
    }

    public function test_it_filters_the_custom_apps_on_the_given_client_id(): void
    {
        $this->dbalConnection
            ->expects($this->once())
            ->method('fetchAssociative')
            ->willReturnCallback(function (string $sql, array $params): array {
                $this->assertStringContainsString('akeneo_connectivity_test_app', $sql);
                $this->assertMatchesRegularExpression('/WHERE\s+app\.client_id\s*=\s*:id/i', $sql);
                $this->assertMatchesRegularExpression('/COUNT\(connected_app\.id\)/i', $sql);
                $this->assertMatchesRegularExpression('/THE_AUTHOR_EXPRESSION\s+AS\s+author/i', $sql);
                $this->assertSame(['id' => '6ff52991-1a3b-4d4a-b4c4-a0e1cd1a4ad9'], $params);

                return $this->row('0');
            });

        $this->sut->execute('6ff52991-1a3b-4d4a-b4c4-a0e1cd1a4ad9');
    }

    /**
     * @return array<string, string|null>
     */
    private function row(string $connected): array
    {
        return [
            'id' => '6ff52991-1a3b-4d4a-b4c4-a0e1cd1a4ad9',
            'name' => 'App prototype',
            'author' => 'Julia Stark',
            'activate_url' => 'https://custom-app.example.com/activate',
            'callback_url' => 'https://custom-app.example.com/callback',
            'connected' => $connected,
        ];
    }
}
