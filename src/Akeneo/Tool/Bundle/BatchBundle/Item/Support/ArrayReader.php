<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Item\Support;

use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;

/**
 * Simple reader that provides data from an array
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class ArrayReader implements ItemReaderInterface
{
    protected $readIndex = 0;
    protected $items;

    /**
     * @return $this
     */
    public function setItems(array $items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $item = null;

        if ($this->readIndex < (is_countable($this->items) ? count($this->items) : 0)) {
            $item = $this->items[$this->readIndex];
            $this->readIndex++;
        }

        return $item;
    }
}
