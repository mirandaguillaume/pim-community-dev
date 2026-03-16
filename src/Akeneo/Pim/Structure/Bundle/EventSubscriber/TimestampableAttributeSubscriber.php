<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Updates the updated date of attributes on attribute option creation or removal
 * This is already done by the TimestampableSubscriber unless the attribute has more than 10 000 options
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: StorageEvents::PRE_SAVE, method: 'setAttributeUpdatedDate')]
#[AsEventListener(event: StorageEvents::PRE_REMOVE, method: 'setAttributeUpdatedDate')]
class TimestampableAttributeSubscriber
{
    public function __construct(private readonly ObjectManager $em)
    {
    }

    public function setAttributeUpdatedDate(GenericEvent $event): void
    {
        $option = $event->getSubject();

        if (!$option instanceof AttributeOptionInterface) {
            return;
        }

        $attribute = $option->getAttribute();

        if (!$attribute instanceof AttributeInterface || null === $attribute->getId()) {
            return;
        }

        $attribute->setUpdated(new \DateTime('now', new \DateTimeZone('UTC')));
        $this->em->persist($attribute);
    }
}
