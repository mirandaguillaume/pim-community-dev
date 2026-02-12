<?php

namespace Akeneo\Tool\Component\Batch\Event;

use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Invalid Item Event
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class InvalidItemEvent extends Event implements EventInterface
{
    /** @var array */
    protected $reasonParameters;

    /** @var InvalidItemInterface */
    protected $item;

    /**
     * @param string                $class
     * @param string                $reason
     */
    public function __construct(InvalidItemInterface $item, protected $class, protected $reason, array $reasonParameters)
    {
        $this->item = $item;
        $this->reasonParameters = $reasonParameters;
    }

    /**
     * Get the class which encountered the invalid item
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Get the reason why the item is invalid
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Get the reason parameters
     *
     * @return array
     */
    public function getReasonParameters()
    {
        return $this->reasonParameters;
    }

    /**
     * Get the invalid item
     *
     * @return InvalidItemInterface
     */
    public function getItem()
    {
        return $this->item;
    }
}
