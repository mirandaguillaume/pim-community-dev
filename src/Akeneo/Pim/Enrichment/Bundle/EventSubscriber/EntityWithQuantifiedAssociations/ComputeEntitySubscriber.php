<?php

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithQuantifiedAssociations;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductModelCodesQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetUuidMappingQueryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Computes the raw quantified association from the QuantifiedAssociation VO,
 * so that doctrine is able to persist the changes in DB.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: StorageEvents::PRE_SAVE, method: 'computeRawQuantifiedAssociations')]
final class ComputeEntitySubscriber
{
    public function __construct(
        protected GetUuidMappingQueryInterface $getUuidMappingQuery,
        protected GetIdMappingFromProductModelCodesQueryInterface $getIdMappingFromProductModelCodes
    ) {
    }

    /**
     * Normalizes product values into "storage" format, and sets the result as raw values.
     */
    public function computeRawQuantifiedAssociations(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof EntityWithQuantifiedAssociationsInterface) {
            return;
        }

        $productIdentifiers = $subject->getQuantifiedAssociationsProductIdentifiers();
        $productUuids = $subject->getQuantifiedAssociationsProductUuids();
        $productModelCodes = $subject->getQuantifiedAssociationsProductModelCodes();

        $uuidMappedProductIdentifiers = $this->getUuidMappingQuery->fromProductIdentifiers($productIdentifiers, $productUuids);
        $mappedProductModelCodes = $this->getIdMappingFromProductModelCodes->execute($productModelCodes);

        $subject->updateRawQuantifiedAssociations(
            $uuidMappedProductIdentifiers,
            $mappedProductModelCodes
        );
    }
}
