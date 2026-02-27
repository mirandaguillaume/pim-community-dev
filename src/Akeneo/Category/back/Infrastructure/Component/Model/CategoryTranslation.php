<?php

namespace Akeneo\Category\Infrastructure\Component\Model;

use Akeneo\Tool\Component\Localization\Model\AbstractTranslation;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Category translation entity.
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity()]
#[ORM\Table(name: 'pim_catalog_category_translation')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[ORM\UniqueConstraint(name: 'locale_foreign_key_idx', columns: ['locale', 'foreign_key'])]
class CategoryTranslation extends AbstractTranslation implements CategoryTranslationInterface
{
    #[ORM\ManyToOne(targetEntity: \Akeneo\Category\Infrastructure\Component\Model\CategoryInterface::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'foreign_key', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $foreignKey;
    /** @var string */
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    protected $label;

    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param CategoryInterface $foreignKey
     */
    public function setForeignKey($foreignKey): self
    {
        $this->foreignKey = $foreignKey;

        return $this;
    }

    public function getForeignKey(): CategoryInterface
    {
        return $this->foreignKey;
    }
}
