<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventSubscribers;

use Akeneo\Connectivity\Connection\Domain\Webhook\Event\MessageProcessedEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Repository\EventsApiDebugRepositoryInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Flush the logs persisted (but not yet saved) by the repository once a Message has been fully processed.
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: MessageProcessedEvent::class, method: 'flushLogs')]
final readonly class EventsApiLoggingSubscriber
{
    public function __construct(private EventsApiDebugRepositoryInterface $eventsApiDebugRepository)
    {
    }

    public function flushLogs(): void
    {
        $this->eventsApiDebugRepository->flush();
    }
}
