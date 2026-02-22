<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\OAuth;

/**
 * Replaces the refresh token model from oauth2-php.
 */
interface IOAuth2RefreshToken
{
    public function getToken(): string;

    public function getClientId(): string;

    public function getExpiresAt(): ?int;

    public function hasExpired(): bool;

    public function getData(): mixed;

    public function getScope(): ?string;
}
