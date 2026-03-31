<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\BatchBundle\EntityManager;

use Akeneo\Tool\Bundle\BatchBundle\EntityManager\PersistedConnectionEntityManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PersistedConnectionEntityManagerTest extends TestCase
{
    private EntityManagerInterface|MockObject $entityManager;
    private PersistedConnectionEntityManager $sut;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->sut = new PersistedConnectionEntityManager($this->entityManager);
    }

    public function test_it_refreshes_connection_when_getting_connection(): void
    {
        $connection = $this->createMock(Connection::class);
        $result = $this->createMock(Result::class);

        $this->entityManager->method('getConnection')->willReturn($connection);
        $connection->method('executeQuery')->with('SELECT 1')->willReturn($result);
        $this->assertSame($connection, $this->sut->getConnection());
    }

    public function test_it_reconnects_when_connection_is_lost(): void
    {
        $connection = $this->createMock(Connection::class);

        $this->entityManager->method('getConnection')->willReturn($connection);
        $connection->method('executeQuery')->with('SELECT 1')->willThrowException(new \Exception('Connection lost'));
        $connection->expects($this->once())->method('close');
        $this->assertSame($connection, $this->sut->getConnection());
    }

    public function test_it_refreshes_connection_when_flushing_data(): void
    {
        $connection = $this->createMock(Connection::class);
        $result = $this->createMock(Result::class);

        $this->entityManager->method('getConnection')->willReturn($connection);
        $connection->method('executeQuery')->with('SELECT 1')->willReturn($result);
        $this->entityManager->expects($this->once())->method('flush');
        $this->sut->flush();
    }
}
