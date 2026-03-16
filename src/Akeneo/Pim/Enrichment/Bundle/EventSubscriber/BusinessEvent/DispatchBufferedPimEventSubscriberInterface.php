<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent;

use Symfony\Component\EventDispatcher\GenericEvent;

interface DispatchBufferedPimEventSubscriberInterface
{
    public function createAndDispatchPimEvents(GenericEvent $postSaveEvent): void;
    public function dispatchBufferedPimEvents(): void;
}
