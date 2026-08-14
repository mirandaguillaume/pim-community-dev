<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\HasUserConsentForAppQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\HasUserConsentForAppQuery;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HasUserConsentForAppQueryTest extends TestCase
{
    private DbalConnection|MockObject $connection;
    private HasUserConsentForAppQuery $sut;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(DbalConnection::class);
        $this->sut = new HasUserConsentForAppQuery($this->connection);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(HasUserConsentForAppQuery::class, $this->sut);
        $this->assertInstanceOf(HasUserConsentForAppQueryInterface::class, $this->sut);
    }

    public function test_it_returns_true_when_the_user_already_consented_for_the_app(): void
    {
        $this->connection
            ->expects($this->once())
            ->method('fetchOne')
            ->with(
                $this->stringContains('akeneo_connectivity_user_consent'),
                ['userId' => 42, 'appId' => 'an_app_id'],
            )
            ->willReturn('1');

        $this->assertTrue($this->sut->execute(42, 'an_app_id'));
    }

    public function test_it_returns_false_when_the_user_never_consented_for_the_app(): void
    {
        $this->connection
            ->expects($this->once())
            ->method('fetchOne')
            ->with(
                $this->stringContains('akeneo_connectivity_user_consent'),
                ['userId' => 42, 'appId' => 'an_app_id'],
            )
            ->willReturn('0');

        $this->assertFalse($this->sut->execute(42, 'an_app_id'));
    }

    public function test_it_scopes_the_count_to_both_the_user_and_the_app(): void
    {
        $executedSql = null;
        $this->connection
            ->expects($this->once())
            ->method('fetchOne')
            ->willReturnCallback(function (string $sql) use (&$executedSql): string {
                $executedSql = $sql;

                return '0';
            });

        $this->sut->execute(42, 'an_app_id');

        $this->assertNotNull($executedSql);
        $this->assertStringContainsString('WHERE user_id = :userId AND app_id = :appId', $executedSql);
    }
}
