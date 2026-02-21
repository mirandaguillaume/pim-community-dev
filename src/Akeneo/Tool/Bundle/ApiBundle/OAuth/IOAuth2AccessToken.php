<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\OAuth;

/**
 * Replaces OAuth2\Model\IOAuth2AccessToken.
 */
interface IOAuth2AccessToken
{
    public function getToken(): string;

    public function getClientId(): string;

    public function getExpiresAt(): ?int;

    public function hasExpired(): bool;

    /**
     * Returns the user data associated with this token.
     */
    public function getData(): mixed;

    public function getScope(): ?string;
}
