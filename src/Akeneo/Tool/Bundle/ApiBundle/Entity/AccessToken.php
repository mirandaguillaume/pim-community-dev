<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Entity;

use Akeneo\Tool\Bundle\ApiBundle\OAuth\IOAuth2AccessToken;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\TokenInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Standalone OAuth access token entity.
 * Replaces the FOS OAuthServerBundle base AccessToken entity.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
#[ORM\Entity]
#[ORM\Table(name: 'pim_api_access_token')]
class AccessToken implements IOAuth2AccessToken, TokenInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    protected $id;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    protected $token;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(name: 'client', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    protected $client;

    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $user;

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
