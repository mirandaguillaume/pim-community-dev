<?php

namespace Acme\Bundle\AppBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractReferenceData;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Acme Color entity (used as simple reference data)
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity(repositoryClass: \Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ReferenceDataRepository::class)]
#[ORM\Table(name: 'acme_reference_data_color')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class Color extends AbstractReferenceData implements ReferenceDataInterface
{
    /** @var string */
    #[ORM\Column(type: Types::STRING, length: 255)]
    protected $name;

    /** @var string */
    #[ORM\Column(type: Types::STRING, length: 255)]
    protected $hex;

    /** @var int */
    #[ORM\Column(type: Types::INTEGER)]
    protected $red;

    /** @var int */
    #[ORM\Column(type: Types::INTEGER)]
    protected $green;

    /** @var int */
    #[ORM\Column(type: Types::INTEGER)]
    protected $blue;

    /** @var int */
    #[ORM\Column(type: Types::INTEGER)]
    protected $hue;

    /** @var int */
    #[ORM\Column(type: Types::INTEGER)]
    protected $hslSaturation;

    /** @var int */
    #[ORM\Column(type: Types::INTEGER)]
    protected $light;

    /** @var int */
    #[ORM\Column(type: Types::INTEGER)]
    protected $hsvSaturation;

    /** @var int */
    #[ORM\Column(type: Types::INTEGER)]
    protected $value;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getHex()
    {
        return $this->hex;
    }

    /**
     * @param string $hex
     */
    public function setHex($hex)
    {
        $this->hex = $hex;
    }

    /**
     * @return int
     */
    public function getRed()
    {
        return $this->red;
    }

    /**
     * @param int $red
     */
    public function setRed($red)
    {
        $this->red = $red;
    }

    /**
     * @return int
     */
    public function getGreen()
    {
        return $this->green;
    }

    /**
     * @param int $green
     */
    public function setGreen($green)
    {
        $this->green = $green;
    }

    /**
     * @return int
     */
    public function getBlue()
    {
        return $this->blue;
    }

    /**
     * @param int $blue
     */
    public function setBlue($blue)
    {
        $this->blue = $blue;
    }

    /**
     * @return int
     */
    public function getHue()
    {
        return $this->hue;
    }

    /**
     * @param int $hue
     */
    public function setHue($hue)
    {
        $this->hue = $hue;
    }

    /**
     * @return int
     */
    public function getHslSaturation()
    {
        return $this->hslSaturation;
    }

    /**
     * @param int $hslSaturation
     */
    public function setHslSaturation($hslSaturation)
    {
        $this->hslSaturation = $hslSaturation;
    }

    /**
     * @return int
     */
    public function getLight()
    {
        return $this->light;
    }

    /**
     * @param int $light
     */
    public function setLight($light)
    {
        $this->light = $light;
    }

    /**
     * @return int
     */
    public function getHsvSaturation()
    {
        return $this->hsvSaturation;
    }

    /**
     * @param int $hsvSaturation
     */
    public function setHsvSaturation($hsvSaturation)
    {
        $this->hsvSaturation = $hsvSaturation;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public static function getLabelProperty()
    {
        return 'name';
    }
}
