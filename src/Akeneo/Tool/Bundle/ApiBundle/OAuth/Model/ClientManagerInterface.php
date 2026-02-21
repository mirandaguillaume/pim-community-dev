<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\OAuth\Model;

/**
 * Replaces FOS\OAuthServerBundle\Model\ClientManagerInterface.
 * Manages OAuth client lifecycle (create, update, delete, find).
 */
interface ClientManagerInterface
{
    /**
     * Creates a new client instance (not yet persisted).
     */
    public function createClient(): ClientInterface;

    /**
     * Persists/updates a client.
     */
    public function updateClient(ClientInterface $client): void;

    /**
     * Deletes a client.
     */
    public function deleteClient(ClientInterface $client): void;

    /**
     * Finds a client by its public id (e.g. "3_randomId").
     */
    public function findClientByPublicId(string $publicId): ?ClientInterface;

    /**
     * Finds a single client by arbitrary criteria.
     */
    public function findClientBy(array $criteria): ?ClientInterface;
}
