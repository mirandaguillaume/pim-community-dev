<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Entity;

use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Standalone OAuth client entity.
 * Replaces the FOS OAuthServerBundle base Client entity.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
#[ORM\Entity]
#[ORM\Table(name: 'pim_api_client')]
class Client implements ClientInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    protected $id;

    #[ORM\Column(name: 'random_id', type: Types::STRING, length: 255, nullable: true)]
    protected $randomId;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected $secret;

    #[ORM\Column(name: 'redirect_uris', type: Types::ARRAY, nullable: true)]
    protected $redirectUris = [];

    #[ORM\Column(name: 'allowed_grant_types', type: Types::ARRAY, nullable: true)]
    protected $allowedGrantTypes = [];

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected $label;

    #[ORM\Column(name: 'marketplace_public_app_id', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $marketplacePublicAppId = null;

    public function getId(): int
    {
        \assert(null !== $this->id, 'Client must be persisted before calling getId()');

        return $this->id;
    }

    public function getRandomId(): ?string
    {
        return $this->randomId;
    }

    public function setRandomId(?string $randomId): void
    {
        $this->randomId = $randomId;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    public function checkSecret(?string $secret): bool
    {
        return null === $this->secret || $secret === $this->secret;
    }

    public function getPublicId(): string
    {
        return sprintf('%s_%s', $this->getId(), $this->getRandomId());
    }

    public function getRedirectUris(): array
    {
        return $this->redirectUris;
    }

    public function setRedirectUris(array $redirectUris): void
    {
        $this->redirectUris = $redirectUris;
    }

    public function getAllowedGrantTypes(): array
    {
        return $this->allowedGrantTypes;
    }

    public function setAllowedGrantTypes(array $grantTypes): void
    {
        $this->allowedGrantTypes = $grantTypes;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function getMarketplacePublicAppId(): ?string
    {
        return $this->marketplacePublicAppId;
    }

    public function setMarketplacePublicAppId(string $marketplacePublicAppId): void
    {
        $this->marketplacePublicAppId = $marketplacePublicAppId;
    }
}
