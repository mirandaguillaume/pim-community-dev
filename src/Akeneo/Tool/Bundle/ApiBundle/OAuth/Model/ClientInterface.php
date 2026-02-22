<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\OAuth\Model;

/**
 * Replaces FOS\OAuthServerBundle\Model\ClientInterface.
 * Provides the contract that the Client entity and ClientManager depend on.
 */
interface ClientInterface
{
    public function getId(): int;

    public function getPublicId(): string;

    public function getSecret(): ?string;

    public function setSecret(?string $secret): void;

    public function getRandomId(): ?string;

    public function setRandomId(?string $randomId): void;

    public function getRedirectUris(): array;

    public function setRedirectUris(array $redirectUris): void;

    public function getAllowedGrantTypes(): array;

    public function setAllowedGrantTypes(array $grantTypes): void;

    public function checkSecret(?string $secret): bool;
}
