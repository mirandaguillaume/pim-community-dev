<?php

namespace Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\InvalidItemEvent;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Collect invalid items
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: EventInterface::INVALID_ITEM, method: 'collect')]
class InvalidItemsCollector
{
    /** @var array */
    protected $invalidItems;

    /**
     * Collect unique invalid items
     */
    public function collect(InvalidItemEvent $event): void
    {
        $invalidItem = $event->getItem();
        $itemData = $invalidItem instanceof FileInvalidItem ? ['position' => $invalidItem->getItemPosition()] : $invalidItem->getInvalidData();
        $this->invalidItems[$this->getHashKey($itemData)] = $event->getItem();
    }

    /**
     * Get invalid items
     *
     * @return array
     */
    public function getInvalidItems()
    {
        return $this->invalidItems;
    }

    /**
     * Get an unique hash for the given item
     *
     *
     * @return string
     */
    protected function getHashKey(array $item): string
    {
        return md5(serialize($item));
    }
}
