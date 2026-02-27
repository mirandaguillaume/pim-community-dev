<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Tool\Component\Localization\Model\AbstractTranslation;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Group translation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity()]
#[ORM\Table(name: 'pim_catalog_group_translation')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[ORM\UniqueConstraint(name: 'locale_foreign_key_idx', columns: ['locale', 'foreign_key'])]
class GroupTranslation extends AbstractTranslation implements GroupTranslationInterface
{
    /** All required columns are mapped through inherited superclass */

    /** Change foreign key to add constraint and work with basic entity */
    #[ORM\ManyToOne(targetEntity: \Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'foreign_key', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $foreignKey;

    /** @var string */
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    protected $label;

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->label;
    }
}
