<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Tool\Component\Localization\Model\AbstractTranslation;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity()]
#[ORM\Table(name: 'pim_catalog_family_variant_translation')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[ORM\UniqueConstraint(name: 'locale_foreign_key_idx', columns: ['locale', 'foreign_key'])]
class FamilyVariantTranslation extends AbstractTranslation implements FamilyVariantTranslationInterface
{
    #[ORM\ManyToOne(targetEntity: \Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'foreign_key', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $foreignKey;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $label = null;

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel(string $label)
    {
        $this->label = $label;
    }
}
