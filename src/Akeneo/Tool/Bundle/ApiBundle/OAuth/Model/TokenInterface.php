<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\OAuth\Model;

/**
 * Replaces FOS\OAuthServerBundle\Model\TokenInterface.
 */
interface TokenInterface
{
    public function getToken(): string;

    public function getClientId(): string;

    public function getExpiresAt(): ?int;

    public function hasExpired(): bool;

    public function getData();

    public function getScope(): ?string;
}
