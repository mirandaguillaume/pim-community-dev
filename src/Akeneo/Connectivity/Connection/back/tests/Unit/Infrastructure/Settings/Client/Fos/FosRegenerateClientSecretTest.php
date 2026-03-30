<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Settings\Client\Fos;

use Akeneo\Connectivity\Connection\Application\Settings\Service\RegenerateClientSecretInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Connectivity\Connection\Infrastructure\Settings\Client\Fos\FosRegenerateClientSecret;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientManagerInterface;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FosRegenerateClientSecretTest extends TestCase
{
    private ClientManagerInterface|MockObject $clientManager;
    private Connection|MockObject $dbalConnection;
    private FosRegenerateClientSecret $sut;

    protected function setUp(): void
    {
        $this->clientManager = $this->createMock(ClientManagerInterface::class);
        $this->dbalConnection = $this->createMock(Connection::class);
        $this->sut = new FosRegenerateClientSecret($this->clientManager, $this->dbalConnection);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FosRegenerateClientSecret::class, $this->sut);
        $this->assertInstanceOf(RegenerateClientSecretInterface::class, $this->sut);
    }

    public function test_it_regenerates_a_client_secret(): void
    {
        $client = $this->createMock(Client::class);

        $clientId = new ClientId(1);
        $this->clientManager->method('findClientBy')->with(['id' => $clientId->id()])->willReturn($client);
        $client->expects($this->once())->method('setSecret')->with($this->isType('string'));
        $this->clientManager->expects($this->once())->method('updateClient')->with($client);
        $this->dbalConnection->expects($this->once())->method('executeStatement')->with(
            'DELETE FROM pim_api_access_token WHERE client = :client_id',
            ['client_id' => $clientId->id()]
        );
        $this->dbalConnection->expects($this->once())->method('executeStatement')->with(
            'DELETE FROM pim_api_refresh_token WHERE client = :client_id',
            ['client_id' => $clientId->id()]
        );
        $this->sut->execute($clientId);
    }

    public function test_it_throws_an_exception_if_client_not_found(): void
    {
        $clientId = new ClientId(123);
        $this->clientManager->method('findClientBy')->with(['id' => $clientId->id()])->willReturn(null);
        $this->clientManager->expects($this->never())->method('updateClient')->with($this->anything());
        $this->dbalConnection->expects($this->never())->method('executeStatement')->with($this->anything());
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('Client with id "123" not found.');
        $this->sut->execute($clientId);
    }
}
