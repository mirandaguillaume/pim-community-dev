<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Clear the internal cache between each batch during the execution of an item step.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: EventInterface::ITEM_STEP_AFTER_BATCH, method: 'clearCache')]
class ClearBatchCacheSubscriber
{
    public function __construct(private readonly EntityManagerClearerInterface $cacheClearer)
    {
    }

    public function clearCache(): void
    {
        $this->cacheClearer->clear();
    }
}
