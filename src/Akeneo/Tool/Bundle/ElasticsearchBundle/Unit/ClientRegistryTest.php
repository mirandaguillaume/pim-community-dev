<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ElasticsearchBundle;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\ClientRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClientRegistryTest extends TestCase
{
    private ClientRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new ClientRegistry();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ClientRegistry::class, $this->sut);
    }

    public function test_it_registers_an_es_client(): void
    {
        $client = $this->createMock(Client::class);

        $this->sut->register($client);
        $this->assertSame([$client], $this->sut->getClients());
    }

    public function test_it_registers_multiple_es_clients(): void
    {
        $client1 = $this->createMock(Client::class);
        $client2 = $this->createMock(Client::class);

        $this->sut->register($client1);
        $this->sut->register($client2);
        $this->assertSame([$client1, $client2], $this->sut->getClients());
    }

    public function test_it_returns_an_empty_list_when_no_clients_has_been_registered(): void
    {
        $this->assertSame([], $this->sut->getClients());
    }
}
