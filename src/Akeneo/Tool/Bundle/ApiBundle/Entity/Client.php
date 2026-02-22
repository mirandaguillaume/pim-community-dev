<?php

namespace Akeneo\Tool\Bundle\ApiBundle\Entity;

use Akeneo\Tool\Bundle\ApiBundle\OAuth\Model\ClientInterface;

/**
 * Standalone OAuth client entity.
 * Replaces the FOS OAuthServerBundle base Client entity.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Client implements ClientInterface
{
    /** @var int|null */
    protected $id;

    /** @var string|null */
    protected $randomId;

    /** @var string|null */
    protected $secret;

    /** @var array */
    protected $redirectUris = [];

    /** @var array */
    protected $allowedGrantTypes = [];

    /** @var string|null */
    protected $label;

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
