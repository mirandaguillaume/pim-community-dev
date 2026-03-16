<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Connector\Job\JobFileBackuper;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: EventInterface::AFTER_JOB_EXECUTION, method: 'cleanImportFile')]
final readonly class CleanImportFileAfterJobExecutionSubscriber
{
    public function __construct(
        private JobFileBackuper $jobFileBackuper
    ) {
    }

    public function cleanImportFile(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();

        if ($jobExecution->getJobInstance()->getType() !== 'import' || $jobExecution->getStatus()->getValue() === BatchStatus::PAUSED) {
            return;
        }

        $this->jobFileBackuper->clean($jobExecution);
    }
}
