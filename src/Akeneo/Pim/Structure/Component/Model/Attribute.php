<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

/**
 * Product attribute, business code is in AttributeInterface, this class can be overriden in projects
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[Assert\GroupSequenceProvider]
#[ORM\Entity(repositoryClass: \Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeRepository::class)]
#[ORM\Table(name: 'pim_catalog_attribute')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
#[ORM\Index(columns: ['code'], name: 'searchcode_idx')]
#[ORM\UniqueConstraint(name: 'searchunique_idx', columns: ['code', 'entity_type'])]
class Attribute extends AbstractAttribute
{
}
