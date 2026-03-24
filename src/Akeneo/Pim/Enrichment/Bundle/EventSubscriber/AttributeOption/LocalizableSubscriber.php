<?php

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\AttributeOption;

use Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Aims to inject selected locale into loaded attribute option
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsDoctrineListener(event: Events::postLoad)]
final class LocalizableSubscriber
{
    public function __construct(protected CatalogContext $context)
    {
    }

    /**
     * After load
     */
    public function postLoad(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if (!$object instanceof AttributeOptionInterface) {
            return;
        }

        if ($this->context->hasLocaleCode()) {
            $object->setLocale($this->context->getLocaleCode());
        }
    }
}
