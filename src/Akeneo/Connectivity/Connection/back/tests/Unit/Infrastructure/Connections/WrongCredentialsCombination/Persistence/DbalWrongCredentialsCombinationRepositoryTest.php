<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Connections\WrongCredentialsCombination\Persistence;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Write\WrongCredentialsCombination;
use Akeneo\Connectivity\Connection\Infrastructure\Connections\WrongCredentialsCombination\Persistence\DbalWrongCredentialsCombinationRepository;
use Akeneo\Tool\Component\StorageUtils\Database\SqlPlatformHelperInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalWrongCredentialsCombinationRepositoryTest extends TestCase
{
    private Connection|MockObject $connection;
    private SqlPlatformHelperInterface|MockObject $platformHelper;
    private DbalWrongCredentialsCombinationRepository $sut;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->platformHelper = $this->createMock(SqlPlatformHelperInterface::class);
        $this->sut = new DbalWrongCredentialsCombinationRepository($this->connection, $this->platformHelper);
    }

    public function test_it_upserts_the_wrong_credentials_combination(): void
    {
        $this->platformHelper->method('upsertClause')
            ->with(['connection_code', 'username'], ['authentication_date = NOW()'])
            ->willReturn('ON DUPLICATE KEY UPDATE authentication_date = NOW()');

        $this->connection->expects($this->once())
            ->method('executeStatement')
            ->with(
                $this->stringContains('ON DUPLICATE KEY UPDATE authentication_date = NOW()'),
                ['connection_code' => 'erp', 'username' => 'julia'],
            );

        $this->sut->create(new WrongCredentialsCombination('erp', 'julia'));
    }

    public function test_it_finds_no_wrong_credentials_combinations_since_a_given_date(): void
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn([]);
        $this->connection->method('executeQuery')->willReturn($result);

        $combinations = $this->sut->findAll(new \DateTimeImmutable('2026-01-01', new \DateTimeZone('UTC')));

        $this->assertSame([], $combinations->normalize());
    }

    public function test_it_finds_the_wrong_credentials_combinations_since_a_given_date(): void
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAllAssociative')->willReturn([
            ['connection_code' => 'erp', 'users' => \json_encode(['julia' => '2026-01-05 10:00:00'])],
        ]);
        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->with($this->anything(), ['since' => '2026-01-01'])
            ->willReturn($result);

        $combinations = $this->sut->findAll(new \DateTimeImmutable('2026-01-01', new \DateTimeZone('UTC')));

        $expectedDate = new \DateTime('2026-01-05 10:00:00', new \DateTimeZone('UTC'))->format('c');
        $this->assertSame(
            ['erp' => ['code' => 'erp', 'users' => [['username' => 'julia', 'date' => $expectedDate]]]],
            $combinations->normalize(),
        );
    }
}
