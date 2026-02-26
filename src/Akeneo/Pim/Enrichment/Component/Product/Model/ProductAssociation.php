<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Product association entity
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity(repositoryClass: \Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\AssociationRepository::class)]
#[ORM\Table(name: 'pim_catalog_association')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[ORM\UniqueConstraint(name: 'owner_uuid_association_type_id_idx', columns: ['owner_uuid', 'association_type_id'])]
class ProductAssociation extends AbstractAssociation implements ProductAssociationInterface
{

    #[ORM\ManyToOne(targetEntity: \Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface::class)]
    #[ORM\JoinColumn(name: 'association_type_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $associationType;

    #[ORM\ManyToOne(targetEntity: \Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface::class, inversedBy: 'associations', cascade: ['detach'])]
    #[ORM\JoinColumn(name: 'owner_uuid', referencedColumnName: 'uuid', nullable: false, onDelete: 'CASCADE')]
    protected $owner;

    #[ORM\ManyToMany(targetEntity: \Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface::class)]
    #[ORM\JoinTable(name: 'pim_catalog_association_group')]
    #[ORM\JoinColumn(name: 'association_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'group_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $groups;

    #[ORM\ManyToMany(targetEntity: \Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface::class)]
    #[ORM\JoinTable(name: 'pim_catalog_association_product')]
    #[ORM\JoinColumn(name: 'association_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'product_uuid', referencedColumnName: 'uuid', onDelete: 'CASCADE')]
    protected $products;

    #[ORM\ManyToMany(targetEntity: \Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface::class)]
    #[ORM\JoinTable(name: 'pim_catalog_association_product_model')]
    #[ORM\JoinColumn(name: 'association_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'product_model_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $productModels;
}
