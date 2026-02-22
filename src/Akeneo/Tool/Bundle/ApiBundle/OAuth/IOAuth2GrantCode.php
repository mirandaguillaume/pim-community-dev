<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\OAuth;

use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientInterface;

/**
 * Replaces OAuth2\IOAuth2GrantCode.
 * Storage interface for authorization code grant type.
 */
interface IOAuth2GrantCode extends IOAuth2Storage
{
    /**
     * Fetch an authorization code by its code.
     */
    public function getAuthCode(string $code): ?IOAuth2AuthCode;

    /**
     * Create a new authorization code.
     *
     * @param string $code The authorization code string
     * @param ClientInterface $client The client
     * @param mixed $data User data associated with the code
     * @param string $redirectUri Redirect URI
     * @param int|null $expires Expiration timestamp
     * @param string|null $scope The scope
     */
    public function createAuthCode(
        string $code,
        ClientInterface $client,
        mixed $data,
        string $redirectUri,
        ?int $expires,
        ?string $scope = null
    ): void;

    /**
     * Mark an authorization code as used.
     */
    public function markAuthCodeAsUsed(string $code): void;
}
