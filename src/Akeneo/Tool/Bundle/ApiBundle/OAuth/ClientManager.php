<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\OAuth;

use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientInterface;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Replaces the FOS OAuthServerBundle ClientManager.
 * Manages OAuth clients using Doctrine ORM.
 */
class ClientManager implements ClientManagerInterface
{
    private readonly EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly string $clientClass = Client::class,
    ) {
        $this->repository = $this->em->getRepository($this->clientClass);
    }

    public function createClient(): ClientInterface
    {
        /** @var Client $client */
        $client = new $this->clientClass();
        $client->setRandomId(Random::generateToken());
        $client->setSecret(Random::generateToken());

        return $client;
    }

    public function updateClient(ClientInterface $client): void
    {
        $this->em->persist($client);
        $this->em->flush();
    }

    public function deleteClient(ClientInterface $client): void
    {
        $this->em->remove($client);
        $this->em->flush();
    }

    public function findClientByPublicId(string $publicId): ?ClientInterface
    {
        if (!str_contains($publicId, '_')) {
            return null;
        }

        [$id, $randomId] = explode('_', $publicId, 2);

        /** @var Client|null $client */
        $client = $this->repository->findOneBy([
            'id' => (int) $id,
            'randomId' => $randomId,
        ]);

        return $client;
    }

    public function findClientBy(array $criteria): ?ClientInterface
    {
        /** @var Client|null $client */
        $client = $this->repository->findOneBy($criteria);

        return $client;
    }
}
