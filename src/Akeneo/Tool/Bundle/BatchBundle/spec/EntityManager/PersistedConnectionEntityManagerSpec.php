<?php

namespace spec\Akeneo\Tool\Bundle\BatchBundle\EntityManager;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;

class PersistedConnectionEntityManagerSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_refreshes_connection_when_getting_connection($entityManager, Connection $connection, Result $result) {
        $entityManager->getConnection()->willReturn($connection);
        $connection->executeQuery('SELECT 1')->willReturn($result);

        $this->getConnection()->shouldReturn($connection);
    }

    function it_reconnects_when_connection_is_lost(
        $entityManager,
        Connection $connection
    ) {
        $entityManager->getConnection()->willReturn($connection);
        $connection->executeQuery('SELECT 1')->willThrow(new \Exception('Connection lost'));
        $connection->close()->shouldBeCalled();
        $connection->connect()->shouldBeCalled();

        $this->getConnection()->shouldReturn($connection);
    }

    function it_refreshes_connection_when_flushing_data(
        $entityManager,
        Connection $connection,
        Result $result
    ) {
        $entityManager->getConnection()->willReturn($connection);
        $connection->executeQuery('SELECT 1')->willReturn($result);
        $entityManager->flush(null)->shouldBeCalled();

        $this->flush();
    }
}
