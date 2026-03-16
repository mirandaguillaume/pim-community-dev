<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: StorageEvents::POST_SAVE, method: 'aggregateEvent', priority: 10000)]
final class ApiAggregatorForAttributeOptionPostSaveEventSubscriber
{
    private bool $isActivated;

    private array $eventsAttributesOptions;

    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
        $this->isActivated = false;
        $this->eventsAttributesOptions = [];
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
        $attributeOption = $event->getSubject();
        $unitary = $event->getArguments()['unitary'] ?? false;

        if (!$this->isActivated || !$attributeOption instanceof AttributeOptionInterface || !$unitary) {
            return;
        }

        $this->eventsAttributesOptions[$attributeOption->getId()] = $attributeOption;

        $event->setArgument('unitary', false);
    }

    public function dispatchAllEvents(): void
    {
        if (empty($this->eventsAttributesOptions)) {
            return;
        }

        $this->eventDispatcher->dispatch(
            new GenericEvent($this->eventsAttributesOptions),
            StorageEvents::POST_SAVE_ALL
        );
        $this->eventsAttributesOptions = [];
    }
}
