<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\OAuth;

use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Standalone OAuth2 server implementation.
 * Replaces OAuth2\OAuth2 (the oauth2-php library class).
 *
 * Supports grant types: password (user_credentials), refresh_token, authorization_code.
 */
class OAuth2
{
    // Grant type constants (same values as OAuth2\OAuth2)
    public const GRANT_TYPE_AUTH_CODE = 'authorization_code';
    public const GRANT_TYPE_USER_CREDENTIALS = 'password';
    public const GRANT_TYPE_REFRESH_TOKEN = 'refresh_token';
    public const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';
    public const GRANT_TYPE_IMPLICIT = 'token';

    // Error constants (same values as OAuth2\OAuth2)
    public const ERROR_INVALID_REQUEST = 'invalid_request';
    public const ERROR_INVALID_CLIENT = 'invalid_client';
    public const ERROR_UNAUTHORIZED_CLIENT = 'unauthorized_client';
    public const ERROR_INVALID_GRANT = 'invalid_grant';
    public const ERROR_UNSUPPORTED_GRANT_TYPE = 'unsupported_grant_type';
    public const ERROR_INVALID_SCOPE = 'invalid_scope';

    // Token type
    public const TOKEN_TYPE_BEARER = 'bearer';

    // Default token lifetimes in seconds
    private const DEFAULT_ACCESS_TOKEN_LIFETIME = 3600;
    private const DEFAULT_REFRESH_TOKEN_LIFETIME = 1209600; // 14 days

    protected IOAuth2Storage $storage;
    private int $accessTokenLifetime;
    private int $refreshTokenLifetime;

    public function __construct(IOAuth2Storage $storage, array $config = [])
    {
        $this->storage = $storage;
        $this->accessTokenLifetime = $config['access_token_lifetime'] ?? self::DEFAULT_ACCESS_TOKEN_LIFETIME;
        $this->refreshTokenLifetime = $config['refresh_token_lifetime'] ?? self::DEFAULT_REFRESH_TOKEN_LIFETIME;
    }

    /**
     * Verify an access token and return it if valid.
     *
     * @throws OAuth2AuthenticateException
     */
    public function verifyAccessToken(string $tokenParam, ?string $scope = null): IOAuth2AccessToken
    {
        $token = $this->storage->getAccessToken($tokenParam);

        if (null === $token) {
            throw new OAuth2AuthenticateException(
                Response::HTTP_UNAUTHORIZED,
                self::ERROR_INVALID_GRANT,
                'The access token provided is invalid.'
            );
        }

        if ($token->hasExpired()) {
            throw new OAuth2AuthenticateException(
                Response::HTTP_UNAUTHORIZED,
                self::ERROR_INVALID_GRANT,
                'The access token provided has expired.'
            );
        }

        if (null !== $scope && !$this->checkScope($scope, $token->getScope())) {
            throw new OAuth2AuthenticateException(
                Response::HTTP_FORBIDDEN,
                self::ERROR_INVALID_SCOPE,
                'The request requires higher privileges than provided by the access token.'
            );
        }

        return $token;
    }

    /**
     * Process a token request (grant_type=password or grant_type=refresh_token).
     *
     * @throws OAuth2ServerException
     */
    public function grantAccessToken(?Request $request = null): Response
    {
        if (null === $request) {
            throw new OAuth2ServerException(
                Response::HTTP_BAD_REQUEST,
                self::ERROR_INVALID_REQUEST,
                'No request provided.'
            );
        }

        $inputData = 'POST' === $request->getMethod()
            ? $request->request->all()
            : $request->query->all();

        $clientCredentials = $this->getClientCredentials($request, $inputData);

        $client = $this->storage->getClient($clientCredentials[0]);

        if (null === $client || !$this->storage->checkClientCredentials($client, $clientCredentials[1])) {
            throw new OAuth2ServerException(
                Response::HTTP_BAD_REQUEST,
                self::ERROR_INVALID_CLIENT,
                'The client credentials are invalid.'
            );
        }

        $grantType = $inputData['grant_type'] ?? null;

        if (empty($grantType)) {
            throw new OAuth2ServerException(
                Response::HTTP_BAD_REQUEST,
                self::ERROR_INVALID_REQUEST,
                'Missing grant_type parameter.'
            );
        }

        // Check if the grant type is recognized by the server
        $supportedGrantTypes = [
            self::GRANT_TYPE_USER_CREDENTIALS,
            self::GRANT_TYPE_REFRESH_TOKEN,
            self::GRANT_TYPE_AUTH_CODE,
            self::GRANT_TYPE_CLIENT_CREDENTIALS,
            self::GRANT_TYPE_IMPLICIT,
        ];

        if (!in_array($grantType, $supportedGrantTypes, true)) {
            throw new OAuth2ServerException(
                Response::HTTP_BAD_REQUEST,
                self::ERROR_INVALID_REQUEST,
                sprintf('Grant type "%s" is not supported.', $grantType)
            );
        }

        if (!in_array($grantType, $client->getAllowedGrantTypes(), true)) {
            throw new OAuth2ServerException(
                Response::HTTP_BAD_REQUEST,
                self::ERROR_UNAUTHORIZED_CLIENT,
                'The grant type is unauthorized for this client_id.'
            );
        }

        $tokenData = match ($grantType) {
            self::GRANT_TYPE_USER_CREDENTIALS => $this->grantAccessTokenForPassword($client, $inputData),
            self::GRANT_TYPE_REFRESH_TOKEN => $this->grantAccessTokenForRefreshToken($client, $inputData),
            self::GRANT_TYPE_AUTH_CODE => $this->grantAccessTokenForAuthCode($client, $inputData),
            default => throw new OAuth2ServerException(
                Response::HTTP_BAD_REQUEST,
                self::ERROR_UNSUPPORTED_GRANT_TYPE,
                sprintf('Grant type "%s" not supported.', $grantType)
            ),
        };

        $responseData = [
            'access_token' => $tokenData['access_token'],
            'expires_in' => $this->accessTokenLifetime,
            'token_type' => self::TOKEN_TYPE_BEARER,
            'scope' => $tokenData['scope'] ?? null,
        ];

        if (isset($tokenData['refresh_token'])) {
            $responseData['refresh_token'] = $tokenData['refresh_token'];
        }

        // Keep scope in response even when null (backward compatibility)

        return new Response(
            json_encode($responseData, JSON_THROW_ON_ERROR),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-store',
                'Pragma' => 'no-cache',
            ]
        );
    }

    /**
     * Extract client credentials from request (Basic Auth, PHP_AUTH_USER, or body).
     *
     * @return array{0: string, 1: string|null} [client_id, client_secret]
     */
    protected function getClientCredentials(Request $request, array $inputData): array
    {
        // Check for Basic auth header
        $authHeaders = $request->headers->get('Authorization');
        if (null !== $authHeaders && str_starts_with($authHeaders, 'Basic ')) {
            $decoded = base64_decode(substr($authHeaders, 6), true);
            if (false !== $decoded && str_contains($decoded, ':')) {
                [$clientId, $clientSecret] = explode(':', $decoded, 2);
                return [$clientId, $clientSecret];
            }
        }

        // Check PHP_AUTH_USER / PHP_AUTH_PW (Symfony converts these to getUser/getPassword)
        $user = $request->getUser();
        if (null !== $user && '' !== $user) {
            return [$user, $request->getPassword()];
        }

        // Check request body
        $clientId = $inputData['client_id'] ?? null;
        $clientSecret = $inputData['client_secret'] ?? null;

        if (empty($clientId)) {
            throw new OAuth2ServerException(
                Response::HTTP_BAD_REQUEST,
                self::ERROR_INVALID_CLIENT,
                'Client credentials were not found in the headers or body.'
            );
        }

        return [$clientId, $clientSecret];
    }

    /**
     * Handle password grant type.
     */
    private function grantAccessTokenForPassword(ClientInterface $client, array $inputData): array
    {
        $username = $inputData['username'] ?? null;
        $password = $inputData['password'] ?? null;

        if (empty($username) || empty($password)) {
            throw new OAuth2ServerException(
                Response::HTTP_BAD_REQUEST,
                self::ERROR_INVALID_REQUEST,
                'Missing username or password parameter.'
            );
        }

        $userData = $this->storage->checkUserCredentials($client, $username, $password);

        if (false === $userData) {
            throw new OAuth2ServerException(
                Response::HTTP_BAD_REQUEST,
                self::ERROR_INVALID_GRANT,
                'Invalid username and password combination.'
            );
        }

        $scope = $inputData['scope'] ?? null;
        $data = $userData['data'] ?? null;

        $accessToken = $this->generateAccessToken();
        $this->storage->createAccessToken(
            $accessToken,
            $client,
            $data,
            time() + $this->accessTokenLifetime,
            $scope
        );

        $refreshToken = $this->generateAccessToken();
        $this->storage->createRefreshToken(
            $refreshToken,
            $client,
            $data,
            time() + $this->refreshTokenLifetime,
            $scope
        );

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'scope' => $scope,
        ];
    }

    /**
     * Handle refresh_token grant type.
     */
    private function grantAccessTokenForRefreshToken(ClientInterface $client, array $inputData): array
    {
        $refreshTokenParam = $inputData['refresh_token'] ?? null;

        if (empty($refreshTokenParam)) {
            throw new OAuth2ServerException(
                Response::HTTP_BAD_REQUEST,
                self::ERROR_INVALID_REQUEST,
                'Missing refresh_token parameter.'
            );
        }

        $refreshToken = $this->storage->getRefreshToken($refreshTokenParam);

        if (null === $refreshToken || $refreshToken->hasExpired()) {
            throw new OAuth2ServerException(
                Response::HTTP_BAD_REQUEST,
                self::ERROR_INVALID_GRANT,
                'Invalid refresh token.'
            );
        }

        // Invalidate old refresh token
        $this->storage->unsetRefreshToken($refreshTokenParam);

        $scope = $refreshToken->getScope();
        $data = $refreshToken->getData();

        $newAccessToken = $this->generateAccessToken();
        $this->storage->createAccessToken(
            $newAccessToken,
            $client,
            $data,
            time() + $this->accessTokenLifetime,
            $scope
        );

        $newRefreshToken = $this->generateAccessToken();
        $this->storage->createRefreshToken(
            $newRefreshToken,
            $client,
            $data,
            time() + $this->refreshTokenLifetime,
            $scope
        );

        return [
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'scope' => $scope,
        ];
    }

    /**
     * Handle authorization_code grant type.
     */
    private function grantAccessTokenForAuthCode(ClientInterface $client, array $inputData): array
    {
        if (!$this->storage instanceof IOAuth2GrantCode) {
            throw new OAuth2ServerException(
                Response::HTTP_BAD_REQUEST,
                self::ERROR_UNSUPPORTED_GRANT_TYPE,
                'Authorization code grant not supported by storage.'
            );
        }

        $code = $inputData['code'] ?? null;

        if (empty($code)) {
            throw new OAuth2ServerException(
                Response::HTTP_BAD_REQUEST,
                self::ERROR_INVALID_REQUEST,
                'Missing code parameter.'
            );
        }

        $authCode = $this->storage->getAuthCode($code);

        if (null === $authCode || $authCode->hasExpired()) {
            throw new OAuth2ServerException(
                Response::HTTP_BAD_REQUEST,
                self::ERROR_INVALID_GRANT,
                'Authorization code has expired or is invalid.'
            );
        }

        $this->storage->markAuthCodeAsUsed($code);

        $scope = $authCode->getScope();
        $data = $authCode->getData();

        $accessToken = $this->generateAccessToken();
        $this->storage->createAccessToken(
            $accessToken,
            $client,
            $data,
            time() + $this->accessTokenLifetime,
            $scope
        );

        return [
            'access_token' => $accessToken,
            'scope' => $scope,
        ];
    }

    /**
     * Generate a random token string.
     */
    private function generateAccessToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Check if required scope is a subset of available scope.
     */
    private function checkScope(string $requiredScope, ?string $availableScope): bool
    {
        if (null === $availableScope) {
            return false;
        }

        $requiredScopes = explode(' ', $requiredScope);
        $availableScopes = explode(' ', $availableScope);

        return empty(array_diff($requiredScopes, $availableScopes));
    }
}
