<?php

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Bundle\BatchBundle\Notification\Notifier;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Job execution notifier
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/MIT MIT
 */
#[AsEventListener(event: EventInterface::AFTER_JOB_EXECUTION, method: 'afterJobExecution')]
class NotificationSubscriber
{
    private array $notifiers = [];

    public function registerNotifier(Notifier $notifier): void
    {
        $this->notifiers[] = $notifier;
    }

    public function afterJobExecution(JobExecutionEvent $jobExecutionEvent): void
    {
        $jobExecution = $jobExecutionEvent->getJobExecution();

        foreach ($this->notifiers as $notifier) {
            $notifier->notify($jobExecution);
        }
    }
}
