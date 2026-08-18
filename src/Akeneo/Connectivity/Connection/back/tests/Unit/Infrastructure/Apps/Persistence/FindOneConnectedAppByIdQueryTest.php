<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByIdQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\FindOneConnectedAppByIdQuery;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindOneConnectedAppByIdQueryTest extends TestCase
{
    private DbalConnection|MockObject $connection;
    private SqlPlatformHelperInterface|MockObject $platformHelper;
    private FindOneConnectedAppByIdQuery $sut;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(DbalConnection::class);
        $this->platformHelper = $this->createMock(SqlPlatformHelperInterface::class);
        $this->sut = new FindOneConnectedAppByIdQuery($this->connection, $this->platformHelper);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FindOneConnectedAppByIdQuery::class, $this->sut);
        $this->assertInstanceOf(FindOneConnectedAppByIdQueryInterface::class, $this->sut);
    }

    public function test_it_returns_null_when_no_connected_app_matches_the_given_id(): void
    {
        $this->platformHelper->method('conditional')->willReturn('FALSE');

        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturn(false);

        $this->connection
            ->expects($this->once())
            ->method('executeQuery')
            ->with($this->isType('string'), ['id' => 'an_app_id'])
            ->willReturn($result);

        $this->assertNull($this->sut->execute('an_app_id'));
    }

    public function test_it_returns_the_connected_app_matching_the_given_id(): void
    {
        $this->platformHelper->method('conditional')->willReturn('FALSE');

        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturn([
            'id' => 'an_app_id',
            'name' => 'App prototype',
            'logo' => 'https://marketplace.test/app-prototype/logo.png',
            'author' => 'Akeneo',
            'partner' => 'Akeneo partner',
            'categories' => '["E-commerce"]',
            'scopes' => '["read_products","write_products"]',
            'certified' => 1,
            'connection_code' => 'app_prototype_connection_code',
            'user_group_name' => 'app_prototype_group',
            'connection_username' => 'app_prototype_username',
            'is_custom_app' => 0,
            'has_outdated_scopes' => 0,
        ]);

        $this->connection
            ->expects($this->once())
            ->method('executeQuery')
            ->with($this->isType('string'), ['id' => 'an_app_id'])
            ->willReturn($result);

        $connectedApp = $this->sut->execute('an_app_id');

        $this->assertNotNull($connectedApp);
        $this->assertSame('an_app_id', $connectedApp->getId());
        $this->assertSame('App prototype', $connectedApp->getName());
        $this->assertSame(['read_products', 'write_products'], $connectedApp->getScopes());
        $this->assertSame('app_prototype_connection_code', $connectedApp->getConnectionCode());
        $this->assertSame('https://marketplace.test/app-prototype/logo.png', $connectedApp->getLogo());
        $this->assertSame('Akeneo', $connectedApp->getAuthor());
        $this->assertSame('app_prototype_group', $connectedApp->getUserGroupName());
        $this->assertSame('app_prototype_username', $connectedApp->getConnectionUsername());
        $this->assertSame(['E-commerce'], $connectedApp->getCategories());
        $this->assertSame('Akeneo partner', $connectedApp->getPartner());
        $this->assertTrue($connectedApp->isCertified());
        $this->assertFalse($connectedApp->isCustomApp());
        $this->assertFalse($connectedApp->hasOutdatedScopes());
        // The SQL does not select is_pending, the denormalization must default it.
        $this->assertFalse($connectedApp->isPending());
    }

    public function test_it_flags_a_custom_app_through_the_platform_specific_conditional(): void
    {
        $this->platformHelper
            ->expects($this->once())
            ->method('conditional')
            ->with('test_app.client_id IS NULL', 'FALSE', 'TRUE')
            ->willReturn('PLATFORM_IS_CUSTOM_APP');

        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturn([
            'id' => 'a_custom_app_id',
            'name' => 'Custom app',
            'logo' => null,
            'author' => null,
            'partner' => null,
            'categories' => '[]',
            'scopes' => '["read_products"]',
            'certified' => 0,
            'connection_code' => 'custom_app_connection_code',
            'user_group_name' => 'custom_app_group',
            'connection_username' => 'custom_app_username',
            'is_custom_app' => 1,
            'has_outdated_scopes' => 1,
        ]);

        $executedSql = null;
        $this->connection
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturnCallback(function (string $sql, array $params) use (&$executedSql, $result): Result {
                $executedSql = $sql;
                $this->assertSame(['id' => 'a_custom_app_id'], $params);

                return $result;
            });

        $connectedApp = $this->sut->execute('a_custom_app_id');

        $this->assertNotNull($connectedApp);
        $this->assertTrue($connectedApp->isCustomApp());
        $this->assertTrue($connectedApp->hasOutdatedScopes());
        $this->assertFalse($connectedApp->isCertified());
        $this->assertNotNull($executedSql);
        $this->assertStringContainsString('PLATFORM_IS_CUSTOM_APP AS is_custom_app', $executedSql);
        $this->assertStringContainsString('WHERE connected_app.id = :id', $executedSql);
    }
}
