<?php

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithValues;

use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Tool\Component\StorageUtils\Model\StateUpdatedAware;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Load real entity values object from the $rawValues field (ie: values in JSON)
 * when an entity with values is loaded by Doctrine.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsDoctrineListener(event: Events::postLoad, priority: 50)]
final readonly class LoadEntityWithValuesSubscriber
{
    public function __construct(private WriteValueCollectionFactory $valueCollectionFactory)
    {
    }

    /**
     * Here we load the real object values from the raw values field.
     *
     * For products, we also add the identifier as a regular value
     * so that it can be used in the product edit form transparently.
     */
    public function postLoad(LifecycleEventArgs $event)
    {
        $entity = $event->getObject();
        if (!$entity instanceof EntityWithValuesInterface) {
            return;
        }

        $rawValues = $entity->getRawValues();

        $values = $this->valueCollectionFactory->createFromStorageFormat($rawValues);
        $entity->setValues($values);
        if ($entity instanceof StateUpdatedAware) {
            $entity->cleanup();
        }
    }
}
