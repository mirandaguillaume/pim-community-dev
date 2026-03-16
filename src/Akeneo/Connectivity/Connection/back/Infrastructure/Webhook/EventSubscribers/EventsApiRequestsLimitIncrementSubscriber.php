<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventSubscribers;

use Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestFailedEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\EventsApiRequestSucceededEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\MessageProcessedEvent;
use Akeneo\Connectivity\Connection\Domain\Webhook\Persistence\Query\UpdateEventsApiRequestCountQueryInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: EventsApiRequestSucceededEvent::class, method: 'incrementRequestCount')]
#[AsEventListener(event: EventsApiRequestFailedEvent::class, method: 'incrementRequestCount')]
#[AsEventListener(event: MessageProcessedEvent::class, method: 'saveRequestCount')]
final class EventsApiRequestsLimitIncrementSubscriber
{
    private int $count = 0;

    public function __construct(private readonly UpdateEventsApiRequestCountQueryInterface $updateEventsApiRequestCountQuery)
    {
    }

    public function incrementRequestCount(): int
    {
        return ++$this->count;
    }

    public function saveRequestCount(): void
    {
        if (0 === $this->count) {
            return;
        }

        $this->updateEventsApiRequestCountQuery->execute(new \DateTimeImmutable('now', new \DateTimeZone('UTC')), $this->count);
        $this->count = 0;
    }
}
