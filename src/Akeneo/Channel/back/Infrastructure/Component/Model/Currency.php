<?php

namespace Akeneo\Channel\Infrastructure\Component\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
/**
 * Currency entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity(repositoryClass: \Akeneo\Channel\Infrastructure\Doctrine\Repository\CurrencyRepository::class)]
#[ORM\Table(name: 'pim_catalog_currency')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class Currency implements CurrencyInterface, \Stringable
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
    #[ORM\Column(type: Types::STRING, length: 3, unique: true)]
    protected $code;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_activated', type: Types::BOOLEAN)]
    protected $activated;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->activated = true;
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
     * Set id
     *
     * @param int $id
     *
     * @return Currency
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
    public function isActivated()
    {
        return $this->activated;
    }

    /**
     * {@inheritdoc}
     */
    public function toggleActivation()
    {
        $this->activated = !$this->activated;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->code;
    }
}
