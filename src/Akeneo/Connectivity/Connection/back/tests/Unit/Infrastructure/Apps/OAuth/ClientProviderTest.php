<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\OAuth;

use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProvider;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientInterface;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClientProviderTest extends TestCase
{
    private const APP_ID = '90741597-54c5-48a1-98da-a68e7ee0a715';
    private const CALLBACK_URL = 'http://localhost:8080/callback';

    private ClientManagerInterface|MockObject $clientManager;
    private ClientProvider $sut;

    protected function setUp(): void
    {
        $this->clientManager = $this->createMock(ClientManagerInterface::class);
        $this->sut = new ClientProvider($this->clientManager);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ClientProvider::class, $this->sut);
        $this->assertInstanceOf(ClientProviderInterface::class, $this->sut);
    }

    public function test_it_creates_a_client_with_the_app_callback_url_and_the_authorization_code_grant(): void
    {
        $createdClient = new Client();

        $this->clientManager
            ->expects($this->once())
            ->method('findClientBy')
            ->with(['marketplacePublicAppId' => self::APP_ID])
            ->willReturn(null);
        $this->clientManager
            ->expects($this->once())
            ->method('createClient')
            ->willReturn($createdClient);
        $this->clientManager
            ->expects($this->once())
            ->method('updateClient')
            ->with($createdClient);

        $client = $this->sut->findOrCreateClient($this->createApp());

        $this->assertSame($createdClient, $client);
        $this->assertSame([self::CALLBACK_URL], $client->getRedirectUris());
        $this->assertSame(['authorization_code'], $client->getAllowedGrantTypes());
        $this->assertSame(self::APP_ID, $client->getMarketplacePublicAppId());
    }

    public function test_it_returns_the_already_existing_client_without_creating_a_second_one(): void
    {
        $existingClient = new Client();
        $existingClient->setMarketplacePublicAppId(self::APP_ID);

        $this->clientManager
            ->expects($this->once())
            ->method('findClientBy')
            ->with(['marketplacePublicAppId' => self::APP_ID])
            ->willReturn($existingClient);
        $this->clientManager
            ->expects($this->never())
            ->method('createClient');
        $this->clientManager
            ->expects($this->never())
            ->method('updateClient')
            ->with($this->anything());

        $this->assertSame($existingClient, $this->sut->findOrCreateClient($this->createApp()));
    }

    public function test_it_does_not_overwrite_the_redirect_uris_of_an_already_existing_client(): void
    {
        $existingClient = new Client();
        $existingClient->setRedirectUris(['http://localhost:8080/previous-callback']);
        $existingClient->setAllowedGrantTypes(['authorization_code']);

        $this->clientManager->method('findClientBy')->willReturn($existingClient);

        $this->sut->findOrCreateClient($this->createApp());

        $this->assertSame(['http://localhost:8080/previous-callback'], $existingClient->getRedirectUris());
    }

    public function test_it_throws_when_the_client_manager_creates_an_unexpected_client_implementation(): void
    {
        $this->clientManager->method('findClientBy')->willReturn(null);
        $this->clientManager
            ->method('createClient')
            ->willReturn($this->createMock(ClientInterface::class));
        $this->clientManager
            ->expects($this->never())
            ->method('updateClient')
            ->with($this->anything());

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Expected instance of %s, got ', Client::class));

        $this->sut->findOrCreateClient($this->createApp());
    }

    public function test_it_finds_a_client_by_its_marketplace_public_app_id(): void
    {
        $client = new Client();

        $this->clientManager
            ->expects($this->once())
            ->method('findClientBy')
            ->with(['marketplacePublicAppId' => self::APP_ID])
            ->willReturn($client);

        $this->assertSame($client, $this->sut->findClientByAppId(self::APP_ID));
    }

    public function test_it_returns_null_when_no_client_matches_the_app_id(): void
    {
        $this->clientManager
            ->expects($this->once())
            ->method('findClientBy')
            ->with(['marketplacePublicAppId' => self::APP_ID])
            ->willReturn(null);

        $this->assertNull($this->sut->findClientByAppId(self::APP_ID));
    }

    public function test_it_throws_when_the_found_client_is_not_the_expected_implementation(): void
    {
        $this->clientManager
            ->method('findClientBy')
            ->willReturn($this->createMock(ClientInterface::class));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Expected null or instance of %s, got ', Client::class));

        $this->sut->findClientByAppId(self::APP_ID);
    }

    private function createApp(): App
    {
        return App::fromWebMarketplaceValues([
            'id' => self::APP_ID,
            'name' => 'App prototype',
            'logo' => 'http://example.com/logo.png',
            'author' => 'Akeneo',
            'url' => 'http://example.com/app',
            'categories' => ['ecommerce'],
            'activate_url' => 'http://example.com/activate',
            'callback_url' => self::CALLBACK_URL,
        ]);
    }
}
