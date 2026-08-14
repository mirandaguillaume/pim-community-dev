<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\ApiBundle\OAuth;

use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\ClientManager;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ClientManagerTest extends TestCase
{
    private EntityManagerInterface|MockObject $em;
    private EntityRepository|MockObject $repository;
    private ClientManager $sut;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);
        // The repository is resolved in the constructor, so it must be stubbed beforehand.
        $this->em
            ->method('getRepository')
            ->with(Client::class)
            ->willReturn($this->repository);
        $this->sut = new ClientManager($this->em);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ClientManager::class, $this->sut);
        $this->assertInstanceOf(ClientManagerInterface::class, $this->sut);
    }

    public function test_it_creates_an_unpersisted_client_with_a_random_id_and_a_secret(): void
    {
        $this->em->expects($this->never())->method('persist')->with($this->anything());
        $this->em->expects($this->never())->method('flush');

        $client = $this->sut->createClient();

        $this->assertInstanceOf(Client::class, $client);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', (string) $client->getRandomId());
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', (string) $client->getSecret());
        $this->assertNotSame($client->getRandomId(), $client->getSecret());
    }

    public function test_it_creates_a_distinct_random_id_and_secret_for_each_client(): void
    {
        $first = $this->sut->createClient();
        $second = $this->sut->createClient();

        $this->assertNotSame($first->getRandomId(), $second->getRandomId());
        $this->assertNotSame($first->getSecret(), $second->getSecret());
    }

    public function test_it_persists_and_flushes_the_client_on_update(): void
    {
        $client = new Client();

        $this->em->expects($this->once())->method('persist')->with($client);
        $this->em->expects($this->once())->method('flush');
        $this->em->expects($this->never())->method('remove')->with($this->anything());

        $this->sut->updateClient($client);
    }

    public function test_it_removes_and_flushes_the_client_on_delete(): void
    {
        $client = new Client();

        $this->em->expects($this->once())->method('remove')->with($client);
        $this->em->expects($this->once())->method('flush');
        $this->em->expects($this->never())->method('persist')->with($this->anything());

        $this->sut->deleteClient($client);
    }

    public function test_it_finds_a_client_by_its_public_id(): void
    {
        $client = new Client();

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            // identicalTo, and not the default loose comparison, so that dropping the (int) cast fails here.
            ->with($this->identicalTo(['id' => 42, 'randomId' => 'a1b2c3']))
            ->willReturn($client);

        $this->assertSame($client, $this->sut->findClientByPublicId('42_a1b2c3'));
    }

    public function test_it_splits_the_public_id_on_the_first_separator_only(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->identicalTo(['id' => 42, 'randomId' => 'a1b2_c3d4']))
            ->willReturn(null);

        $this->sut->findClientByPublicId('42_a1b2_c3d4');
    }

    public function test_it_finds_back_the_client_from_the_public_id_it_exposes(): void
    {
        $client = new Client();
        $client->setRandomId('e5f6a7b8');
        $this->forceId($client, 7);

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->identicalTo(['id' => 7, 'randomId' => 'e5f6a7b8']))
            ->willReturn($client);

        $this->assertSame($client, $this->sut->findClientByPublicId($client->getPublicId()));
    }

    public function test_it_returns_null_without_querying_when_the_public_id_has_no_separator(): void
    {
        $this->repository->expects($this->never())->method('findOneBy')->with($this->anything());

        $this->assertNull($this->sut->findClientByPublicId('42'));
    }

    public function test_it_returns_null_when_no_client_matches_the_public_id(): void
    {
        $this->repository->method('findOneBy')->willReturn(null);

        $this->assertNull($this->sut->findClientByPublicId('42_a1b2c3'));
    }

    public function test_it_finds_a_client_by_arbitrary_criteria(): void
    {
        $client = new Client();

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['marketplacePublicAppId' => '90741597-54c5-48a1-98da-a68e7ee0a715'])
            ->willReturn($client);

        $this->assertSame(
            $client,
            $this->sut->findClientBy(['marketplacePublicAppId' => '90741597-54c5-48a1-98da-a68e7ee0a715'])
        );
    }

    public function test_it_returns_null_when_no_client_matches_the_criteria(): void
    {
        $this->repository->method('findOneBy')->willReturn(null);

        $this->assertNull($this->sut->findClientBy(['marketplacePublicAppId' => 'unknown']));
    }

    private function forceId(Client $client, int $id): void
    {
        $property = new \ReflectionProperty(Client::class, 'id');
        $property->setAccessible(true);
        $property->setValue($client, $id);
    }
}
