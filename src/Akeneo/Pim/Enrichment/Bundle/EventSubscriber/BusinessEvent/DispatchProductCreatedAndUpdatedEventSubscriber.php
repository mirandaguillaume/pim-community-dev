<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\BusinessEvent;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: StorageEvents::POST_SAVE, method: 'createAndDispatchPimEvents', priority: -10)]
#[AsEventListener(event: StorageEvents::POST_SAVE_ALL, method: 'dispatchBufferedPimEvents', priority: -10)]
final class DispatchProductCreatedAndUpdatedEventSubscriber implements DispatchBufferedPimEventSubscriberInterface
{
    /** @var array<ProductCreated|ProductUpdated> */
    private array $events = [];

    public function __construct(private readonly Security $security, private readonly MessageBusInterface $messageBus, private readonly int $maxBulkSize, private readonly LoggerInterface $logger, private readonly LoggerInterface $loggerBusinessEvent)
    {
    }

    public function createAndDispatchPimEvents(GenericEvent $postSaveEvent): void
    {
        if ($postSaveEvent->hasArgument('force_save') && true === $postSaveEvent->getArgument('force_save')) {
            return;
        }

        /** @var ProductInterface */
        $product = $postSaveEvent->getSubject();
        if (false === $product instanceof ProductInterface) {
            return;
        }

        if (!($user = $this->security->getUser()) instanceof \Symfony\Component\Security\Core\User\UserInterface) {
            return;
        }

        $author = Author::fromUser($user);
        $data = [
            'identifier' => $product->getIdentifier(),
            'uuid' => $product->getUuid(),
        ];

        if ($postSaveEvent->hasArgument('is_new') && true === $postSaveEvent->getArgument('is_new')) {
            $this->events[] = new ProductCreated($author, $data);
        } else {
            $this->events[] = new ProductUpdated($author, $data);
        }

        if ($postSaveEvent->hasArgument('unitary') && true === $postSaveEvent->getArgument('unitary')) {
            $this->dispatchBufferedPimEvents();
        } elseif (count($this->events) >= $this->maxBulkSize) {
            $this->dispatchBufferedPimEvents();
        }
    }

    public function dispatchBufferedPimEvents(): void
    {
        if (count($this->events) === 0) {
            return;
        }

        try {
            $this->messageBus->dispatch(new BulkEvent($this->events));
            $this->loggerBusinessEvent->info(
                json_encode(
                    [
                        'type' => 'business_event.dispatch',
                        'event_count' => count($this->events),
                        'events' => array_map(fn ($event) => [
                            'name' => $event->getName(),
                            'uuid' => $event->getUuid(),
                            'author' => $event->getAuthor()->name(),
                            'author_type' => $event->getAuthor()->type(),
                            'timestamp' => $event->getTimestamp(),
                        ], $this->events),
                    ],
                    JSON_THROW_ON_ERROR
                )
            );
        } catch (TransportException $e) {
            $this->logger->critical($e->getMessage());
        }

        $this->events = [];
    }
}
