<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetConnectedAppScopesQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\GetConnectedAppScopesQuery;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetConnectedAppScopesQueryTest extends TestCase
{
    private DbalConnection|MockObject $dbalConnection;
    private GetConnectedAppScopesQuery $sut;

    protected function setUp(): void
    {
        $this->dbalConnection = $this->createMock(DbalConnection::class);
        $this->sut = new GetConnectedAppScopesQuery($this->dbalConnection);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetConnectedAppScopesQuery::class, $this->sut);
        $this->assertInstanceOf(GetConnectedAppScopesQueryInterface::class, $this->sut);
    }

    public function test_it_returns_the_scopes_granted_to_the_connected_app(): void
    {
        $this->dbalConnection->expects($this->once())
            ->method('fetchOne')
            ->with($this->isType('string'), ['id' => '90741597-54c5-48a1-98da-a68e7ee0a715'])
            ->willReturn('["read_products","write_products","delete_products","openid","profile","email"]');

        $this->assertSame(
            ['read_products', 'write_products', 'delete_products', 'openid', 'profile', 'email'],
            $this->sut->execute('90741597-54c5-48a1-98da-a68e7ee0a715'),
        );
    }

    public function test_it_selects_the_scopes_of_the_given_connected_app_only(): void
    {
        $this->dbalConnection->expects($this->once())
            ->method('fetchOne')
            ->willReturnCallback(function (string $query, array $params): string {
                $this->assertStringContainsString('akeneo_connectivity_connected_app', $query);
                $this->assertStringContainsString('WHERE id = :id', $query);
                $this->assertSame(['id' => '90741597-54c5-48a1-98da-a68e7ee0a715'], $params);

                return '["read_products"]';
            });

        $this->assertSame(['read_products'], $this->sut->execute('90741597-54c5-48a1-98da-a68e7ee0a715'));
    }

    public function test_it_returns_no_scope_when_the_connected_app_does_not_exist(): void
    {
        $this->dbalConnection->method('fetchOne')->willReturn(false);

        $this->assertSame([], $this->sut->execute('an_unknown_app_id'));
    }

    public function test_it_returns_no_scope_when_the_stored_scopes_are_null(): void
    {
        $this->dbalConnection->method('fetchOne')->willReturn(null);

        $this->assertSame([], $this->sut->execute('90741597-54c5-48a1-98da-a68e7ee0a715'));
    }

    public function test_it_returns_no_scope_when_the_stored_scopes_are_an_empty_string(): void
    {
        $this->dbalConnection->method('fetchOne')->willReturn('');

        $this->assertSame([], $this->sut->execute('90741597-54c5-48a1-98da-a68e7ee0a715'));
    }

    public function test_it_returns_an_empty_list_when_the_connected_app_has_an_empty_scope_list(): void
    {
        $this->dbalConnection->method('fetchOne')->willReturn('[]');

        $this->assertSame([], $this->sut->execute('90741597-54c5-48a1-98da-a68e7ee0a715'));
    }

    public function test_it_throws_when_the_stored_scopes_are_not_valid_json(): void
    {
        $this->dbalConnection->method('fetchOne')->willReturn('not a json payload');

        $this->expectException(\JsonException::class);

        $this->sut->execute('90741597-54c5-48a1-98da-a68e7ee0a715');
    }
}
