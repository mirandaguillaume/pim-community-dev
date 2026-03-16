<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\EventListener;

use Akeneo\Tool\Bundle\MessengerBundle\Stamp\ReceiverStamp;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Using Google Pub/Sub we should ack the message within the next 10 seconds after pulling it (it can be configured
 * to 10 minutes maximum). After that the message is deliver again. As the job execution can be longer, we
 * take the decision to ack the message just after pulling it. The aim to this subscriber is to ack all job messages
 * before the job execution.
 *
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
#[AsEventListener(event: WorkerMessageReceivedEvent::class, method: 'addReceiverStamp')]
final readonly class AddReceiverStampEventListener
{
    public function __construct(private ContainerInterface $receiverLocator)
    {
    }

    public function addReceiverStamp(WorkerMessageReceivedEvent $event): void
    {
        $receiver = $this->receiverLocator->get($event->getReceiverName());
        $event->addStamps(new ReceiverStamp($receiver));
    }
}
