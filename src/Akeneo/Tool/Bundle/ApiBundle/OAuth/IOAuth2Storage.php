<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\OAuth;

use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientInterface;

/**
 * Replaces OAuth2\IOAuth2Storage.
 * Minimum storage interface needed by the OAuth2 server.
 */
interface IOAuth2Storage
{
    /**
     * Verify a client's credentials.
     *
     * @return array{client: ClientInterface, pass: bool}|false
     */
    public function checkClientCredentials(ClientInterface $client, ?string $clientSecret = null): bool;

    /**
     * Look up the supplied oauth_token from storage.
     *
     * @return IOAuth2AccessToken|null
     */
    public function getAccessToken(string $oauthToken): ?IOAuth2AccessToken;

    /**
     * Store the supplied access token values to storage.
     *
     * @return IOAuth2AccessToken
     */
    public function createAccessToken(
        string $oauthToken,
        ClientInterface $client,
        mixed $data,
        ?int $expires,
        ?string $scope = null
    ): IOAuth2AccessToken;

    /**
     * Check user credentials for "password" grant type.
     *
     * @return array{data: mixed}|false
     */
    public function checkUserCredentials(ClientInterface $client, string $username, string $password);

    /**
     * Create a refresh token.
     */
    public function createRefreshToken(
        string $refreshToken,
        ClientInterface $client,
        mixed $data,
        ?int $expires,
        ?string $scope = null
    ): mixed;

    /**
     * Get a stored refresh token.
     *
     * @return IOAuth2RefreshToken|null
     */
    public function getRefreshToken(string $refreshToken): ?IOAuth2RefreshToken;

    /**
     * Remove the supplied refresh token.
     */
    public function unsetRefreshToken(string $refreshToken): void;

    /**
     * Look up a client by client_id.
     */
    public function getClient(string $clientId): ?ClientInterface;
}
