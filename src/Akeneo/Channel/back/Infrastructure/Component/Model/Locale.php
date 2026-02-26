<?php

namespace Akeneo\Channel\Infrastructure\Component\Model;

use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Locale entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity(repositoryClass: \Akeneo\Channel\Infrastructure\Doctrine\Repository\LocaleRepository::class)]
#[ORM\Table(name: 'pim_catalog_locale')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class Locale implements LocaleInterface, VersionableInterface, \Stringable
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: Types::STRING, length: 20, unique: true)]
    protected $code;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_activated', type: Types::BOOLEAN)]
    protected $activated = false;

    /**
     * @var ArrayCollection
     */
    #[ORM\ManyToMany(targetEntity: \Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface::class, mappedBy: 'locales', cascade: ['detach'])]
    protected $channels;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->channels = new ArrayCollection();
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguage()
    {
        return (null === $this->code) ? null : substr($this->code, 0, 2);
    }

    /**
     * {@inheritdoc}
     */
    public function isActivated(): bool
    {
        return $this->activated;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChannel(ChannelInterface $channel)
    {
        return $this->channels->contains($channel);
    }

    /**
     * {@inheritdoc}
     */
    public function addChannel(ChannelInterface $channel)
    {
        if (!$this->channels->contains($channel)) {
            $this->channels->add($channel);
            $this->activated = true;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChannel(ChannelInterface $channel)
    {
        $this->channels->removeElement($channel);
        if ($this->channels->count() === 0) {
            $this->activated = false;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return null !== $this->code ? \Locale::getDisplayName($this->code) : null;
    }
}
