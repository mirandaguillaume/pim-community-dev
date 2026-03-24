<?php

namespace Akeneo\Tool\Bundle\BatchBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\InvalidItemEvent;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Event\StepExecutionEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Subscriber to log job execution result
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
#[AsEventListener(event: EventInterface::JOB_EXECUTION_CREATED, method: 'jobExecutionCreated')]
#[AsEventListener(event: EventInterface::BEFORE_JOB_EXECUTION, method: 'beforeJobExecution')]
#[AsEventListener(event: EventInterface::JOB_EXECUTION_STOPPED, method: 'jobExecutionStopped')]
#[AsEventListener(event: EventInterface::JOB_EXECUTION_INTERRUPTED, method: 'jobExecutionInterrupted')]
#[AsEventListener(event: EventInterface::JOB_EXECUTION_FATAL_ERROR, method: 'jobExecutionFatalError')]
#[AsEventListener(event: EventInterface::BEFORE_JOB_STATUS_UPGRADE, method: 'beforeJobStatusUpgrade')]
#[AsEventListener(event: EventInterface::BEFORE_STEP_EXECUTION, method: 'beforeStepExecution')]
#[AsEventListener(event: EventInterface::STEP_EXECUTION_SUCCEEDED, method: 'stepExecutionSucceeded')]
#[AsEventListener(event: EventInterface::STEP_EXECUTION_INTERRUPTED, method: 'stepExecutionInterrupted')]
#[AsEventListener(event: EventInterface::STEP_EXECUTION_ERRORED, method: 'stepExecutionErrored')]
#[AsEventListener(event: EventInterface::STEP_EXECUTION_COMPLETED, method: 'stepExecutionCompleted')]
#[AsEventListener(event: EventInterface::INVALID_ITEM, method: 'invalidItem')]
class LoggerSubscriber
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var string */
    protected $translationLocale = 'en';

    /** @var string */
    protected $translationDomain = 'messages';

    public function __construct(LoggerInterface $logger, TranslatorInterface $translator)
    {
        $this->logger = $logger;
        $this->translator = $translator;
    }

    /**
     * Set the translation locale
     *
     * @param string $translationLocale
     */
    public function setTranslationLocale($translationLocale)
    {
        $this->translationLocale = $translationLocale;
    }

    /**
     * Set the translation domain
     *
     * @param string $translationDomain
     */
    public function setTranslationDomain($translationDomain)
    {
        $this->translationDomain = $translationDomain;
    }

    /**
     * Log the job execution creation
     */
    public function jobExecutionCreated(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->debug(sprintf('Job execution is created: %s', $jobExecution));
    }

    /**
     * Log the job execution before the job execution
     */
    public function beforeJobExecution(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->debug(sprintf('Job execution starting: %s', $jobExecution));
    }

    /**
     * Log the job execution when the job execution stopped
     */
    public function jobExecutionStopped(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->info(sprintf('Job execution was stopped: %s', $jobExecution));
    }

    /**
     * Log the job execution when the job execution was interrupted
     */
    public function jobExecutionInterrupted(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->info(sprintf('Encountered interruption executing job: %s', $jobExecution));
        $this->logger->debug('Full exception', ['exception', $jobExecution->getFailureExceptions()]);
    }

    /**
     * Log the job execution when a fatal error was raised during job execution
     */
    public function jobExecutionFatalError(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->error(
            'Encountered fatal error executing job',
            ['exception', $jobExecution->getFailureExceptions()]
        );
    }

    /**
     * Log the job execution before its status is upgraded
     */
    public function beforeJobStatusUpgrade(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        $this->logger->debug(sprintf('Upgrading JobExecution status: %s', $jobExecution));
    }

    /**
     * Log the step execution before the step execution
     */
    public function beforeStepExecution(StepExecutionEvent $event)
    {
        $stepExecution = $event->getStepExecution();

        $this->logger->info(sprintf('Step execution starting: %s', $stepExecution));
    }

    /**
     * Log the step execution when the step execution succeeded
     */
    public function stepExecutionSucceeded(StepExecutionEvent $event)
    {
        $stepExecution = $event->getStepExecution();

        $this->logger->debug(sprintf('Step execution success: id= %d', $stepExecution->getId()));
    }

    /**
     * Log the step execution when the step execution was interrupted
     */
    public function stepExecutionInterrupted(StepExecutionEvent $event)
    {
        $stepExecution = $event->getStepExecution();

        $this->logger->info(
            sprintf('Encountered interruption executing step: %s', $stepExecution->getFailureExceptionMessages())
        );
        $this->logger->debug('Full exception', ['exception', $stepExecution->getFailureExceptions()]);
    }

    /**
     * Log the step execution when the step execution was errored
     *
     */
    public function stepExecutionErrored(StepExecutionEvent $event)
    {
        $stepExecution = $event->getStepExecution();
        $e = $event->getException();

        $this->logger->error($e, ['exception' => $e]);

        $this->logger->warning(
            sprintf(
                'Encountered an error executing the step: %s',
                implode(
                    ', ',
                    array_map(
                        fn ($exception) => $this->translator->trans(
                            $exception['message'],
                            $exception['messageParameters'],
                            $this->translationDomain,
                            $this->translationLocale
                        ),
                        $stepExecution->getFailureExceptions()
                    )
                )
            )
        );
    }

    /**
     * Log the step execution when the step execution was completed
     */
    public function stepExecutionCompleted(StepExecutionEvent $event)
    {
        $stepExecution = $event->getStepExecution();

        $this->logger->info(sprintf('Step execution complete: %s', $stepExecution));
    }

    /**
     * Log invalid item event
     */
    public function invalidItem(InvalidItemEvent $event)
    {
        $this->logger->warning(
            sprintf(
                'The %s was unable to handle the following item: %s (REASON: %s)',
                $event->getClass(),
                $this->formatAsString($event->getItem()->getInvalidData()),
                $this->translator->trans(
                    $event->getReason(),
                    $event->getReasonParameters(),
                    $this->translationDomain,
                    $this->translationLocale
                )
            )
        );
    }

    /**
     * Format anything as a string
     *
     *
     * @return string
     */
    private function formatAsString(mixed $data)
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $result[] = sprintf(
                    '%s => %s',
                    $this->formatAsString($key),
                    $this->formatAsString($value)
                );
            }

            return sprintf("[%s]", implode(', ', $result));
        }

        if (is_bool($data)) {
            return $data ? 'true' : 'false';
        }

        if ($data instanceof \DateTime) {
            return $data->format('Y-m-d');
        }

        return (string) $data;
    }
}
