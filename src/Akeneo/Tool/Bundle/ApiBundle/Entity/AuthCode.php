<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Entity;

use Akeneo\Tool\Bundle\ApiBundle\OAuth\IOAuth2AuthCode;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Standalone OAuth authorization code entity.
 * Replaces the FOS OAuthServerBundle base AuthCode entity.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
#[ORM\Entity]
#[ORM\Table(name: 'pim_api_auth_code')]
class AuthCode implements IOAuth2AuthCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    protected $id;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    protected $token;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $client;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $user;

    #[ORM\Column(name: 'redirect_uri', type: Types::STRING, length: 255, nullable: true)]
    protected $redirectUri;

    #[ORM\Column(name: 'expires_at', type: Types::INTEGER, nullable: true)]
    protected $expiresAt;

    #[ORM\Column(type: Types::STRING, length: 1000, nullable: true)]
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

    public function getRedirectUri(): ?string
    {
        return $this->redirectUri;
    }

    public function setRedirectUri(?string $redirectUri): void
    {
        $this->redirectUri = $redirectUri;
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
