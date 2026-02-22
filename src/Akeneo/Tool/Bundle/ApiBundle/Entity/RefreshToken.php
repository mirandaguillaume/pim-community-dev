<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Entity;

use Akeneo\Tool\Bundle\ApiBundle\OAuth\IOAuth2RefreshToken;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\TokenInterface;

/**
 * Standalone OAuth refresh token entity.
 * Replaces the FOS OAuthServerBundle base RefreshToken entity.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RefreshToken implements IOAuth2RefreshToken, TokenInterface
{
    /** @var int|null */
    protected $id;

    /** @var string|null */
    protected $token;

    /** @var Client|null */
    protected $client;

    /** @var mixed */
    protected $user;

    /** @var int|null */
    protected $expiresAt;

    /** @var string|null */
    protected $scope;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token ?? '';
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient($client): void
    {
        $this->client = $client;
    }

    public function getClientId(): string
    {
        return $this->client?->getPublicId() ?? '';
    }

    public function getData(): mixed
    {
        return $this->user;
    }

    public function getUser(): mixed
    {
        return $this->user;
    }

    public function setUser($user): void
    {
        $this->user = $user;
    }

    public function getExpiresAt(): ?int
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?int $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    public function hasExpired(): bool
    {
        if (null === $this->expiresAt) {
            return false;
        }

        return time() > $this->expiresAt;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(?string $scope): void
    {
        $this->scope = $scope;
    }
}
