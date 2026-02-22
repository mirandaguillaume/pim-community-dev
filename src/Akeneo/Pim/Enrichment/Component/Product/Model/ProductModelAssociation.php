<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity()]
#[ORM\Table(name: 'pim_catalog_product_model_association')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[ORM\UniqueConstraint(name: 'locale_foreign_key_idx', columns: ['owner_id', 'association_type_id'])]
class ProductModelAssociation extends AbstractAssociation implements ProductModelAssociationInterface
{

    #[ORM\ManyToOne(targetEntity: \Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface::class)]
    #[ORM\JoinColumn(name: 'association_type_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $associationType;

    #[ORM\ManyToOne(targetEntity: \Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface::class, inversedBy: 'associations', cascade: ['detach'])]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $owner;

    #[ORM\ManyToMany(targetEntity: \Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface::class)]
    #[ORM\JoinTable(name: 'pim_catalog_association_product_model_to_group')]
    #[ORM\JoinColumn(name: 'association_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'group_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $groups;

    #[ORM\ManyToMany(targetEntity: \Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface::class)]
    #[ORM\JoinTable(name: 'pim_catalog_association_product_model_to_product')]
    #[ORM\JoinColumn(name: 'association_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'product_uuid', referencedColumnName: 'uuid', onDelete: 'CASCADE')]
    protected $products;

    #[ORM\ManyToMany(targetEntity: \Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface::class)]
    #[ORM\JoinTable(name: 'pim_catalog_association_product_model_to_product_model')]
    #[ORM\JoinColumn(name: 'association_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'product_model_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $productModels;
}
