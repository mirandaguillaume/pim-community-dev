<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\IsCustomAppsNumberLimitReachedQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\IsCustomAppsNumberLimitReachedQuery;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Service\GetCustomAppsNumberLimit;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsCustomAppsNumberLimitReachedQueryTest extends TestCase
{
    private DbalConnection|MockObject $dbalConnection;

    protected function setUp(): void
    {
        $this->dbalConnection = $this->createMock(DbalConnection::class);
    }

    public function test_it_is_initializable(): void
    {
        $sut = new IsCustomAppsNumberLimitReachedQuery($this->dbalConnection, new GetCustomAppsNumberLimit(5));

        $this->assertInstanceOf(IsCustomAppsNumberLimitReachedQuery::class, $sut);
        $this->assertInstanceOf(IsCustomAppsNumberLimitReachedQueryInterface::class, $sut);
    }

    public function test_it_returns_false_when_less_custom_apps_than_the_limit_exist(): void
    {
        $this->mockCount('3');
        $sut = new IsCustomAppsNumberLimitReachedQuery($this->dbalConnection, new GetCustomAppsNumberLimit(5));

        $this->assertFalse($sut->execute());
    }

    public function test_it_returns_false_when_exactly_one_custom_app_slot_remains(): void
    {
        $this->mockCount('4');
        $sut = new IsCustomAppsNumberLimitReachedQuery($this->dbalConnection, new GetCustomAppsNumberLimit(5));

        $this->assertFalse($sut->execute());
    }

    public function test_it_returns_true_when_the_custom_apps_count_equals_the_limit(): void
    {
        $this->mockCount('5');
        $sut = new IsCustomAppsNumberLimitReachedQuery($this->dbalConnection, new GetCustomAppsNumberLimit(5));

        $this->assertTrue($sut->execute());
    }

    public function test_it_returns_true_when_the_custom_apps_count_exceeds_the_limit(): void
    {
        $this->mockCount('6');
        $sut = new IsCustomAppsNumberLimitReachedQuery($this->dbalConnection, new GetCustomAppsNumberLimit(5));

        $this->assertTrue($sut->execute());
    }

    public function test_it_returns_false_when_no_custom_app_exists_and_the_limit_is_positive(): void
    {
        $this->mockCount('0');
        $sut = new IsCustomAppsNumberLimitReachedQuery($this->dbalConnection, new GetCustomAppsNumberLimit(1));

        $this->assertFalse($sut->execute());
    }

    public function test_it_returns_true_when_the_limit_is_zero(): void
    {
        $this->mockCount('0');
        $sut = new IsCustomAppsNumberLimitReachedQuery($this->dbalConnection, new GetCustomAppsNumberLimit(0));

        $this->assertTrue($sut->execute());
    }

    public function test_it_counts_the_rows_of_the_custom_apps_table(): void
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchOne')->willReturn('3');

        $this->dbalConnection
            ->expects($this->once())
            ->method('executeQuery')
            ->willReturnCallback(function (string $sql) use ($result): Result {
                $this->assertMatchesRegularExpression('/SELECT\s+COUNT\(\*\)/i', $sql);
                $this->assertStringContainsString('akeneo_connectivity_test_app', $sql);

                return $result;
            });

        $sut = new IsCustomAppsNumberLimitReachedQuery($this->dbalConnection, new GetCustomAppsNumberLimit(5));

        $this->assertFalse($sut->execute());
    }

    private function mockCount(string $count): void
    {
        $result = $this->createMock(Result::class);
        $result->expects($this->once())->method('fetchOne')->willReturn($count);

        $this->dbalConnection->expects($this->once())->method('executeQuery')->willReturn($result);
    }
}
