<?php

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithQuantifiedAssociations;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindQuantifiedAssociationTypeCodesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductModelIdsQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetUuidMappingQueryInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Load quantified associations into a QuantifiedAssociation Value Object.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsDoctrineListener(event: Events::postLoad, priority: 55)]
final readonly class LoadEntitySubscriber
{
    public function __construct(
        private GetUuidMappingQueryInterface $getUuidMappingQuery,
        private GetIdMappingFromProductModelIdsQueryInterface $getIdMappingFromProductModelIds,
        private FindQuantifiedAssociationTypeCodesInterface $findQuantifiedAssociationTypeCodes
    ) {
    }

    /**
     * Here we load the real object values from the raw values field.
     *
     * For products, we also add the identifier as a regular value
     * so that it can be used in the product edit form transparently.
     */
    public function postLoad(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof EntityWithQuantifiedAssociationsInterface) {
            return;
        }

        $productIds = $entity->getQuantifiedAssociationsProductIds();
        $productUuids = $entity->getQuantifiedAssociationsProductUuids();
        $productModelIds = $entity->getQuantifiedAssociationsProductModelIds();

        $mappedProductIds = $this->getUuidMappingQuery->fromProductIds($productIds, $productUuids);
        $mappedProductModelIds = $this->getIdMappingFromProductModelIds->execute($productModelIds);
        $quantifiedAssociationTypeCodes = $this->findQuantifiedAssociationTypeCodes->execute();

        $entity->hydrateQuantifiedAssociations(
            $mappedProductIds,
            $mappedProductModelIds,
            $quantifiedAssociationTypeCodes
        );
    }
}
