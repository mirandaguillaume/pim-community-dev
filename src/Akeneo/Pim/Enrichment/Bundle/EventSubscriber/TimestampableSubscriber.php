<?php

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Aims to add timestambable behavior
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class TimestampableSubscriber
{
    /**
     * Before insert
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if (!$object instanceof TimestampableInterface) {
            return;
        }

        $object->setCreated(new \DateTime('now', new \DateTimeZone('UTC')));
        $object->setUpdated(new \DateTime('now', new \DateTimeZone('UTC')));
    }

    /**
     * Before update
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $object = $args->getObject();

        if (!$object instanceof TimestampableInterface) {
            return;
        }

        // Timestamps are managed by the VersioningBundle in this case
        if ($object instanceof VersionableInterface) {
            return;
        }

        $object->setUpdated(new \DateTime('now', new \DateTimeZone('UTC')));
    }
}
