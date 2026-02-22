<?php

namespace Acme\Bundle\AppBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractReferenceData;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

/**
 * Acme Fabric entity (used as multi reference data)
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity(repositoryClass: \Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ReferenceDataRepository::class)]
#[ORM\Table(name: 'acme_reference_data_fabric')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class Fabric extends AbstractReferenceData implements ReferenceDataInterface
{
    /** @var string */
    #[ORM\Column(type: Types::STRING, length: 255)]
    protected $name;

    /** @var int */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected $alternativeName;

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
     * Set year
     *
     * @param int $year
     */
    public function setAlternativeName($year)
    {
        $this->alternativeName = $year;
    }

    /**
     * Get year
     *
     * @return int
     */
    public function getAlternativeName()
    {
        return $this->alternativeName;
    }

    /**
     * {@inheritdoc}
     */
    public static function getLabelProperty()
    {
        return 'name';
    }
}
