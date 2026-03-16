<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\EventSubscribers;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\CacheClearerInterface;
use Akeneo\Connectivity\Connection\Domain\Webhook\Event\MessageProcessedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Clear all caches (LRU, ...) when a Message has been fully processed.
 * The goal is to ensure that update to permissions, etc... are up-to-date in the context of the Events API consumer.
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: MessageProcessedEvent::class, method: 'clearCache')]
final readonly class EventsApiClearCacheSubscriber
{
    public function __construct(private CacheClearerInterface $cacheClearer)
    {
    }

    public function clearCache(): void
    {
        $this->cacheClearer->clear();
    }
}
