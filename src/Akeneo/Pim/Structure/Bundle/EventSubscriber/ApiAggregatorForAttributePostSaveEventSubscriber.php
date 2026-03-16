<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: StorageEvents::POST_SAVE, method: 'aggregateEvent', priority: 10000)]
final class ApiAggregatorForAttributePostSaveEventSubscriber
{
    private bool $isActivated;

    private array $eventsAttributes;

    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
        $this->isActivated = false;
        $this->eventsAttributes = [];
    }

    public function activate(): void
    {
        $this->isActivated = true;
    }

    public function deactivate(): void
    {
        $this->isActivated = false;
    }

    public function aggregateEvent(GenericEvent $event)
    {
        $attribute = $event->getSubject();
        $unitary = $event->getArguments()['unitary'] ?? false;

        if (!$this->isActivated || !$attribute instanceof AttributeInterface || !$unitary) {
            return;
        }

        $this->eventsAttributes[$attribute->getId()] = $attribute;

        $event->setArgument('unitary', false);
    }

    public function dispatchAllEvents(): void
    {
        if (empty($this->eventsAttributes)) {
            return;
        }

        $this->eventDispatcher->dispatch(new GenericEvent($this->eventsAttributes), StorageEvents::POST_SAVE_ALL);
        $this->eventsAttributes = [];
    }
}
