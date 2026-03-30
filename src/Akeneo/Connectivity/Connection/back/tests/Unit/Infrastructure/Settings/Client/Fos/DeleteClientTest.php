<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Settings\Client\Fos;

use Akeneo\Connectivity\Connection\Application\Settings\Service\DeleteClientInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Connectivity\Connection\Infrastructure\Settings\Client\Fos\DeleteClient;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteClientTest extends TestCase
{
    private ClientManagerInterface|MockObject $clientManager;
    private DeleteClient $sut;

    protected function setUp(): void
    {
        $this->clientManager = $this->createMock(ClientManagerInterface::class);
        $this->sut = new DeleteClient($this->clientManager);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(DeleteClient::class, $this->sut);
        $this->assertInstanceOf(DeleteClientInterface::class, $this->sut);
    }

    public function test_it_deletes_a_client(): void
    {
        $client = new Client();
        $clientId = new ClientId(1);
        $this->clientManager->method('findClientBy')->with(['id' => $clientId->id()])->willReturn($client);
        $this->clientManager->expects($this->once())->method('deleteClient')->with($client);
        $this->sut->execute($clientId);
    }

    public function test_it_throws_an_exception_if_client_not_found(): void
    {
        $clientId = new ClientId(1);
        $this->clientManager->method('findClientBy')->with(['id' => $clientId->id()])->willReturn(null);
        $this->clientManager->expects($this->never())->method('deleteClient')->with($this->anything());
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('Client with id "1" not found.');
        $this->sut->execute($clientId);
    }
}
