<?php

namespace Akeneo\Tool\Component\Localization\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

/**
 * Abstract translation class
 */
#[ORM\MappedSuperclass]
abstract class AbstractTranslation
{
    /** @var int */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    protected $id;

    /** @var string */
    #[ORM\Column(type: Types::STRING, length: 20)]
    protected $locale;

    /** @var mixed */
    protected $foreignKey;

    /**
     * Get id
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set locale
     *
     * @param string $locale
     *
     * @return AbstractTranslation
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set foreignKey
     *
     *
     * @return AbstractTranslation
     */
    public function setForeignKey(mixed $foreignKey): self
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    /**
     * Get foreignKey
     *
     * @return mixed
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }
}
