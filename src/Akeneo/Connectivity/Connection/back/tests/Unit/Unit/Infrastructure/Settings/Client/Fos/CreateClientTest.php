<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Settings\Client\Fos;

use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateClientInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Client;
use Akeneo\Connectivity\Connection\Infrastructure\Settings\Client\Fos\CreateClient;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client as FosClient;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientManagerInterface;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\OAuth2;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateClientTest extends TestCase
{
    private ClientManagerInterface|MockObject $clientManager;
    private CreateClient $sut;

    protected function setUp(): void
    {
        $this->clientManager = $this->createMock(ClientManagerInterface::class);
        $this->sut = new CreateClient($this->clientManager);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CreateClient::class, $this->sut);
        $this->assertInstanceOf(CreateClientInterface::class, $this->sut);
    }

    public function test_it_creates_a_client_with_a_label(): void
    {
        $fosClient = $this->createMock(Client::class);

        $this->clientManager->method('createClient')->willReturn($fosClient);
        $fosClient->expects($this->once())->method('setLabel')->with('new_app');
        $fosClient->expects($this->once())->method('setAllowedGrantTypes')->with([OAuth2::GRANT_TYPE_USER_CREDENTIALS, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $this->clientManager->expects($this->once())->method('updateClient')->with($fosClient);
        $fosClient->method('getId')->willReturn(1);
        $fosClient->method('getPublicId')->willReturn('1_myclientid');
        $fosClient->method('getSecret')->willReturn('my_client_secret');
        $clientVO = $this->execute('new_app');
        $clientVO->shouldBeAnInstanceOf(Client::class);
        $clientVO->id()->shouldReturn(1);
        $clientVO->clientId()->shouldReturn('1_myclientid');
        $clientVO->secret()->shouldReturn('my_client_secret');
    }
}
