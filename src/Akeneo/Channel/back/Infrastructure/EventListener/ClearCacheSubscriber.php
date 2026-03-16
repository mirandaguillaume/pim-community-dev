<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\EventListener;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Clear channel cache on save.
 *
 * @author    jmleroux <jean-marie.leroux@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: StorageEvents::POST_SAVE, method: 'clearCache')]
#[AsEventListener(event: StorageEvents::POST_SAVE_ALL, method: 'clearCache')]
class ClearCacheSubscriber
{
    public function __construct(private readonly CachedQueryInterface $cachedChannelExistsWithLocale)
    {
    }

    /**
     * Clear Locale cache
     */
    public function clearCache(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof ChannelInterface) {
            return;
        }

        $this->cachedChannelExistsWithLocale->clearCache();
    }
}
