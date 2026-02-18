<?php

namespace Akeneo\Pim\Enrichment\Bundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FixArrayToStringListener implements EventSubscriberInterface
{
    /**
     * @param string $delimiter
     */
    public function __construct(private $delimiter)
    {
    }

    public function preBind(FormEvent $event)
    {
        $value = $event->getData();
        if (is_array($value)) {
            $event->setData(implode($this->delimiter, $value));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [FormEvents::PRE_SUBMIT => 'preBind'];
    }
}
