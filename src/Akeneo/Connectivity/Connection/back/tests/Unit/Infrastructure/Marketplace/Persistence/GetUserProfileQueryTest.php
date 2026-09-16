<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Marketplace\Persistence;

use Akeneo\Connectivity\Connection\Domain\Marketplace\GetUserProfileQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Persistence\GetUserProfileQuery;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUserProfileQueryTest extends TestCase
{
    private DbalConnection|MockObject $dbalConnection;
    private GetUserProfileQuery $sut;

    protected function setUp(): void
    {
        $this->dbalConnection = $this->createMock(DbalConnection::class);
        $this->sut = new GetUserProfileQuery($this->dbalConnection);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetUserProfileQuery::class, $this->sut);
        $this->assertInstanceOf(GetUserProfileQueryInterface::class, $this->sut);
    }

    public function test_it_returns_the_profile_of_the_given_user(): void
    {
        $this->dbalConnection
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturn('product_manager');

        $this->assertSame('product_manager', $this->sut->execute('julia'));
    }

    public function test_it_returns_null_when_the_user_has_no_profile(): void
    {
        $this->dbalConnection->method('fetchOne')->willReturn(null);

        $this->assertNull($this->sut->execute('julia'));
    }

    public function test_it_looks_up_the_profile_by_username_in_the_user_table(): void
    {
        $this->dbalConnection
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturnCallback(function (string $sql, array $params): string {
                $this->assertStringContainsString('oro_user', $sql);
                $this->assertMatchesRegularExpression('/SELECT\s+profile/i', $sql);
                $this->assertMatchesRegularExpression('/WHERE\s+username\s*=\s*:username/i', $sql);
                $this->assertSame(['username' => 'julia'], $params);

                return 'product_manager';
            });

        $this->assertSame('product_manager', $this->sut->execute('julia'));
    }
}
