<?php

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Set the job execution log file into the job execution instance
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
#[AsEventListener(event: EventInterface::BEFORE_JOB_EXECUTION, method: 'setJobExecutionLogFile')]
class SetJobExecutionLogFileSubscriber
{
    protected \Akeneo\Tool\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler $logger;

    public function __construct(BatchLogHandler $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Set the job execution log file
     */
    public function setJobExecutionLogFile(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();
        $jobExecution->setLogFile(
            $this->logger->getFilename()
        );
    }
}
