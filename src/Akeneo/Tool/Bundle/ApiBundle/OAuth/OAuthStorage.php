<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\OAuth;

use Akeneo\Tool\Bundle\ApiBundle\Entity\AccessToken;
use Akeneo\Tool\Bundle\ApiBundle\Entity\AuthCode;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Akeneo\Tool\Bundle\ApiBundle\Entity\RefreshToken;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Replaces FOS\OAuthServerBundle\Storage\OAuthStorage.
 * Doctrine-based OAuth2 storage implementation.
 */
class OAuthStorage implements IOAuth2GrantCode
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserProviderInterface $userProvider,
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        private readonly string $clientClass = Client::class,
        private readonly string $accessTokenClass = AccessToken::class,
        private readonly string $refreshTokenClass = RefreshToken::class,
        private readonly string $authCodeClass = AuthCode::class,
    ) {
    }

    public function getClient(string $clientId): ?ClientInterface
    {
        // clientId can be in the format "id_randomId"
        if (str_contains($clientId, '_')) {
            $parts = explode('_', $clientId, 2);
            $id = (int) $parts[0];
            $randomId = $parts[1];

            /** @var Client|null $client */
            $client = $this->em->getRepository($this->clientClass)->findOneBy([
                'id' => $id,
                'randomId' => $randomId,
            ]);
        } else {
            /** @var Client|null $client */
            $client = $this->em->getRepository($this->clientClass)->find($clientId);
        }

        return $client;
    }

    public function checkClientCredentials(ClientInterface $client, ?string $clientSecret = null): bool
    {
        return $client->checkSecret($clientSecret);
    }

    public function getAccessToken(string $oauthToken): ?IOAuth2AccessToken
    {
        /** @var AccessToken|null $token */
        $token = $this->em->getRepository($this->accessTokenClass)->findOneBy(['token' => $oauthToken]);

        return $token;
    }

    public function createAccessToken(
        string $oauthToken,
        ClientInterface $client,
        mixed $data,
        ?int $expires,
        ?string $scope = null
    ): IOAuth2AccessToken {
        /** @var AccessToken $token */
        $token = new $this->accessTokenClass();
        $token->setToken($oauthToken);
        $token->setClient($client);
        $token->setUser($data);
        $token->setExpiresAt($expires);
        $token->setScope($scope);

        $this->em->persist($token);
        $this->em->flush();

        return $token;
    }

    public function checkUserCredentials(ClientInterface $client, string $username, string $password): array|false
    {
        try {
            $user = $this->userProvider->loadUserByIdentifier($username);
        } catch (\Exception) {
            return false;
        }

        $hasher = $this->passwordHasherFactory->getPasswordHasher($user);

        if ($hasher->verify($user->getPassword(), $password, $user->getSalt())) {
            return ['data' => $user];
        }

        return false;
    }

    public function createRefreshToken(
        string $refreshToken,
        ClientInterface $client,
        mixed $data,
        ?int $expires,
        ?string $scope = null
    ): mixed {
        /** @var RefreshToken $token */
        $token = new $this->refreshTokenClass();
        $token->setToken($refreshToken);
        $token->setClient($client);
        $token->setUser($data);
        $token->setExpiresAt($expires);
        $token->setScope($scope);

        $this->em->persist($token);
        $this->em->flush();

        return $token;
    }

    public function getRefreshToken(string $refreshToken): ?IOAuth2RefreshToken
    {
        /** @var RefreshToken|null $token */
        $token = $this->em->getRepository($this->refreshTokenClass)->findOneBy(['token' => $refreshToken]);

        return $token;
    }

    public function unsetRefreshToken(string $refreshToken): void
    {
        $token = $this->em->getRepository($this->refreshTokenClass)->findOneBy(['token' => $refreshToken]);

        if (null !== $token) {
            $this->em->remove($token);
            $this->em->flush();
        }
    }

    public function getAuthCode(string $code): ?IOAuth2AuthCode
    {
        /** @var AuthCode|null $authCode */
        $authCode = $this->em->getRepository($this->authCodeClass)->findOneBy(['token' => $code]);

        return $authCode;
    }

    public function createAuthCode(
        string $code,
        ClientInterface $client,
        mixed $data,
        string $redirectUri,
        ?int $expires,
        ?string $scope = null
    ): mixed {
        /** @var AuthCode $authCode */
        $authCode = new $this->authCodeClass();
        $authCode->setToken($code);
        $authCode->setClient($client);
        $authCode->setUser($data);
        $authCode->setRedirectUri($redirectUri);
        $authCode->setExpiresAt($expires);
        $authCode->setScope($scope);

        $this->em->persist($authCode);
        $this->em->flush();

        return $authCode;
    }

    public function markAuthCodeAsUsed(string $code): void
    {
        $authCode = $this->em->getRepository($this->authCodeClass)->findOneBy(['token' => $code]);

        if (null !== $authCode) {
            $this->em->remove($authCode);
            $this->em->flush();
        }
    }
}
