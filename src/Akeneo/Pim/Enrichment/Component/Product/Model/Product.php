<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

/**
 * Product. An entity with flexible values, completeness, categories, associations and much more...
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[ORM\Entity(repositoryClass: \Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository\ProductRepository::class)]
#[ORM\Table(name: 'pim_catalog_product')]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class Product extends AbstractProduct implements ProductInterface {}
