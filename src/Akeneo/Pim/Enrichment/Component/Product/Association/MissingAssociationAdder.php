<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Association;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;

/**
 * Create all missing associations for each existing association type
 * and add them to an association aware entity.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MissingAssociationAdder
{
    public function __construct(private readonly AssociationTypeRepositoryInterface $associationTypeRepository, private readonly AssociationClassResolver $associationClassResolver) {}

    public function addMissingAssociations(EntityWithAssociationsInterface $entity): void
    {
        $missingAssocTypes = $this->associationTypeRepository->findMissingAssociationTypes($entity);

        if (!empty($missingAssocTypes)) {
            foreach ($missingAssocTypes as $associationType) {
                $associationClass = $this->associationClassResolver->resolveAssociationClass($entity);

                /** @var AssociationInterface $association */
                $association = new $associationClass();
                $association->setAssociationType($associationType);
                $entity->addAssociation($association);
            }
        }
    }
}
