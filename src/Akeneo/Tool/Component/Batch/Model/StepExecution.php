<?php

namespace Akeneo\Tool\Component\Batch\Model;

use Akeneo\Tool\Component\Batch\Item\ExecutionContext;
use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\RuntimeErrorException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

/**
 * Batch domain object representation the execution of a step. Unlike JobExecution, there are additional properties
 * related the processing of items such as commit count, etc.
 *
 * Inspired by Spring Batch  org.springframework.batch.core.StepExecution
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
#[ORM\Entity()]
#[ORM\Table(name: 'akeneo_batch_step_execution')]
class StepExecution implements \Stringable
{
    private const TRACKING_DATA_PROCESSED_ITEMS = 'processedItems';
    private const TRACKING_DATA_TOTAL_ITEMS = 'totalItems';
    private const TRACKING_DATA_DEFAULT = [
        self::TRACKING_DATA_PROCESSED_ITEMS => 0,
        self::TRACKING_DATA_TOTAL_ITEMS => 0,
    ];

    /** @var integer */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private $id;

    /** @var integer */
    #[ORM\Column(type: Types::INTEGER)]
    private $status = null;

    #[ORM\Column(name: 'read_count', type: Types::INTEGER)]
    private int $readCount = 0;

    #[ORM\Column(name: 'write_count', type: Types::INTEGER)]
    private int $writeCount = 0;

    #[ORM\Column(name: 'filter_count', type: Types::INTEGER)]
    private int $filterCount = 0;

    #[ORM\Column(name: 'warning_count', type: Types::INTEGER, options: ['default' => 0])]
    private int $warningCount = 0;

    #[ORM\Column(name: 'start_time', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $startTime = null;

    #[ORM\Column(name: 'end_time', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $endTime = null;

    /* @var ExecutionContext $executionContext */
    private ?\Akeneo\Tool\Component\Batch\Item\ExecutionContext $executionContext = null;

    /* @var ExitStatus */
    private ?\Akeneo\Tool\Component\Batch\Job\ExitStatus $exitStatus = null;

    #[ORM\Column(name: 'exit_code', type: Types::STRING, length: 255, nullable: true)]
    private ?string $exitCode = null;

    #[ORM\Column(name: 'exit_description', type: Types::TEXT, nullable: true)]
    private ?string $exitDescription = null;

    #[ORM\Column(name: 'terminate_only', type: Types::BOOLEAN, nullable: true)]
    private bool $terminateOnly = false;

    #[ORM\Column(name: 'failure_exceptions', type: Types::ARRAY, nullable: true)]
    private ?array $failureExceptions = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $errors = [];

    #[ORM\OneToMany(targetEntity: \Akeneo\Tool\Component\Batch\Model\Warning::class, mappedBy: 'stepExecution', cascade: ['persist'], orphanRemoval: true)]
    private \Doctrine\Common\Collections\Collection $warnings;

    #[ORM\Column(type: Types::ARRAY)]
    private array $summary = [];

    #[ORM\Column(name: 'tracking_data', type: Types::JSON, nullable: true)]
    private array $trackingData = self::TRACKING_DATA_DEFAULT;

    #[ORM\Column(name: 'is_trackable', type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isTrackable;
    #[ORM\Column(name: 'current_state', type: Types::JSON, nullable: true)]
    private ?array $currentState;

    #[ORM\Column(name: 'step_name', type: Types::STRING, length: 100, nullable: true)]
    private $stepName;

    #[ORM\ManyToOne(targetEntity: \Akeneo\Tool\Component\Batch\Model\JobExecution::class, inversedBy: 'stepExecutions')]
    #[ORM\JoinColumn(name: 'job_execution_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private JobExecution $jobExecution;

    /**
     * Constructor with mandatory properties.
     *
     * @param string       $stepName     the step to which this execution belongs
     * @param JobExecution $jobExecution the current job execution
     */
    public function __construct($stepName, JobExecution $jobExecution)
    {
        $this->stepName = $stepName;
        $this->jobExecution = $jobExecution;
        $jobExecution->addStepExecution($this);
        $this->warnings = new ArrayCollection();
        $this->executionContext = new ExecutionContext();
        $this->setStatus(new BatchStatus(BatchStatus::STARTING));
        $this->setExitStatus(new ExitStatus(ExitStatus::EXECUTING));

        $this->failureExceptions = [];
        $this->errors = [];
        $this->isTrackable = false;

        $this->startTime = new \DateTime();
    }

    /**
     * Reset id on clone
     */
    public function __clone()
    {
        $this->id = null;
    }

    /**
     * Get Id
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the {@link ExecutionContext} for this execution
     *
     * @return ExecutionContext with its attributes
     */
    public function getExecutionContext()
    {
        return $this->executionContext;
    }

    /**
     * Sets the {@link ExecutionContext} for this execution
     *
     * @param ExecutionContext $executionContext the attributes
     *
     * @return StepExecution
     */
    public function setExecutionContext(ExecutionContext $executionContext)
    {
        $this->executionContext = $executionContext;

        return $this;
    }

    /**
     * Returns the time that this execution ended
     *
     * @return \DateTime | null time that this execution ended
     */
    public function getEndTime(): ?\DateTime
    {
        return $this->endTime;
    }

    /**
     * Sets the time that this execution ended
     *
     * @param \DateTime $endTime the time that this execution ended
     *
     * @return StepExecution
     */
    public function setEndTime(\DateTime $endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Returns the current number of items read for this execution
     *
     * @return integer the current number of items read for this execution
     */
    public function getReadCount()
    {
        return $this->readCount;
    }

    /**
     * Sets the current number of read items for this execution
     *
     * @param integer $readCount the current number of read items for this execution
     *
     * @return StepExecution
     */
    public function setReadCount($readCount)
    {
        $this->readCount = $readCount;

        return $this;
    }

    /**
     * Increment the read count by 1
     */
    public function incrementReadCount()
    {
        $this->readCount++;
    }

    /**
     * Returns the current number of items written for this execution
     *
     * @return integer the current number of items written for this execution
     */
    public function getWriteCount()
    {
        return $this->writeCount;
    }

    /**
     * Sets the current number of written items for this execution
     *
     * @param integer $writeCount the current number of written items for this execution
     *
     * @return StepExecution
     */
    public function setWriteCount($writeCount)
    {
        $this->writeCount = $writeCount;

        return $this;
    }

    /**
     * Increment the write count by 1
     */
    public function incrementWriteCount()
    {
        $this->writeCount++;
    }

    /**
     * Returns the current number of items filtered out of this execution
     *
     * @return integer the current number of items filtered out of this execution
     */
    public function getFilterCount()
    {
        return $this->readCount - $this->writeCount;
    }

    /**
     * @return boolean flag to indicate that an execution should halt
     */
    public function isTerminateOnly()
    {
        return $this->terminateOnly;
    }

    /**
     * Set a flag that will signal to an execution environment that this
     * execution (and its surrounding job) wishes to exit.
     *
     * @return StepExecution
     */
    public function setTerminateOnly()
    {
        $this->terminateOnly = true;

        return $this;
    }

    /**
     * Gets the time this execution started
     *
     * @return \DateTime The time this execution started
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Sets the time this execution started
     *
     * @param \DateTime $startTime the time this execution started
     *
     * @return StepExecution
     */
    public function setStartTime(\DateTime $startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Returns the current status of this step
     *
     * @return BatchStatus the current status of this step
     */
    public function getStatus()
    {
        return new BatchStatus($this->status);
    }

    /**
     * Sets the current status of this step
     *
     * @param BatchStatus $status the current status of this step
     *
     * @return StepExecution
     */
    public function setStatus(BatchStatus $status)
    {
        $this->status = $status->getValue();

        return $this;
    }

    /**
     * Upgrade the status field if the provided value is greater than the
     * existing one. Clients using this method to set the status can be sure
     * that they don't overwrite a failed status with an successful one.
     *
     * @param mixed $status the new status value
     *
     * @return StepExecution
     */
    public function upgradeStatus(mixed $status)
    {
        $newBatchStatus = $this->getStatus();
        $newBatchStatus->upgradeTo($status);
        $this->setStatus($newBatchStatus);

        return $this;
    }

    /**
     * @return string the name of the step
     */
    public function getStepName()
    {
        return $this->stepName;
    }

    /**
     * @return StepExecution
     */
    public function setExitStatus(ExitStatus $exitStatus)
    {
        $this->exitStatus = $exitStatus;
        $this->exitCode = $exitStatus->getExitCode();
        $this->exitDescription = $exitStatus->getExitDescription();

        return $this;
    }

    /**
     * @return ExitStatus the exit status
     */
    public function getExitStatus()
    {
        return $this->exitStatus;
    }

    /**
     * Accessor for the execution context information of the enclosing job.
     *
     * @return JobExecution the job execution that was used to start this step execution.
     *
     */
    public function getJobExecution()
    {
        return $this->jobExecution;
    }

    /**
     * Accessor for the job parameters
     *
     * @return JobParameters
     *
     */
    public function getJobParameters()
    {
        return $this->jobExecution->getJobParameters();
    }

    /**
     * Get failure exceptions
     * @return mixed
     */
    public function getFailureExceptions()
    {
        return $this->failureExceptions;
    }

    /**
     * Add a failure exception
     *
     * @return StepExecution
     */
    public function addFailureException(\Exception $e)
    {
        $this->failureExceptions[] = [
            'class'             => $e::class,
            'message'           => $e->getMessage(),
            'messageParameters' => $e instanceof RuntimeErrorException ? $e->getMessageParameters() : [],
            'code'              => $e->getCode(),
            'trace'             => $e->getTraceAsString()
        ];

        return $this;
    }

    /**
     * @return string
     */
    public function getFailureExceptionMessages()
    {
        return implode(
            ' ',
            array_map(
                fn ($e) => $e['message'],
                $this->failureExceptions
            )
        );
    }

    /**
     * @param string $message
     *
     * @return StepExecution
     */
    public function addError($message)
    {
        $this->errors[] = $message;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Add a warning
     *
     * @param string               $reason
     */
    public function addWarning($reason, array $reasonParameters, InvalidItemInterface $item)
    {
        $data = $item->getInvalidData();

        if (null === $data) {
            $data = [];
        }

        if (is_object($data)) {
            $id = '[unknown]';
            if (\method_exists($data, 'getUuid')
                && $data::class !== 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct'
            ) {
                $id = $data->getUuid()->toString();
            } elseif (\method_exists($data, 'getId')) {
                $id = $data->getId();
            }

            $data = [
                'class'  => ClassUtils::getClass($data),
                'id'     => $id,
                'string' => method_exists($data, '__toString') ? (string) $data : '[unknown]',
            ];
        }

        $this->warnings->add(
            new Warning(
                $this,
                $reason,
                $reasonParameters,
                $data
            )
        );

        $this->warningCount++;
    }

    /**
     * Get the warnings
     *
     * @return ArrayCollection
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    public function getWarningCount(): int
    {
        return $this->warningCount;
    }

    /**
     * Add row in summary
     *
     * @param string $key
     */
    public function addSummaryInfo($key, mixed $info)
    {
        $this->summary[$key] = $info;
    }

    /**
     * Increment counter in summary
     *
     * @param string  $key
     * @param integer $increment
     */
    public function incrementSummaryInfo($key, $increment = 1)
    {
        if (!isset($this->summary[$key])) {
            $this->summary[$key] = $increment;
        } else {
            $this->summary[$key] = $this->summary[$key] + $increment;
        }
    }

    /**
     * Get a summary row
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getSummaryInfo($key, mixed $defaultValue = '')
    {
        return $this->summary[$key] ?? $defaultValue;
    }

    /**
     * Set summary
     *
     * @param array $summary
     *
     * @return StepExecution
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary
     *
     * @return array
     */
    public function getSummary()
    {
        return $this->summary;
    }

    public function setTotalItems(int $totalItems): void
    {
        if (null === $this->trackingData) {
            $this->trackingData = self::TRACKING_DATA_DEFAULT;
        }

        $this->trackingData[self::TRACKING_DATA_TOTAL_ITEMS] = $totalItems;
    }

    public function getTotalItems(): int
    {
        return $this->trackingData[self::TRACKING_DATA_TOTAL_ITEMS] ?? 0;
    }

    public function incrementProcessedItems(int $increment = 1): void
    {
        if (null === $this->trackingData) {
            $this->trackingData = self::TRACKING_DATA_DEFAULT;
        }

        $this->trackingData[self::TRACKING_DATA_PROCESSED_ITEMS] += $increment;
        if ($this->trackingData[self::TRACKING_DATA_PROCESSED_ITEMS] > $this->getTotalItems()) {
            $this->setTotalItems($this->trackingData[self::TRACKING_DATA_PROCESSED_ITEMS]);
        }
    }

    public function getProcessedItems(): int
    {
        return $this->trackingData[self::TRACKING_DATA_PROCESSED_ITEMS] ?? 0;
    }

    public function getTrackingData(): array
    {
        return $this->trackingData ?? self::TRACKING_DATA_DEFAULT;
    }

    public function setTrackingData(array $trackingData): void
    {
        $this->trackingData = $trackingData;
    }

    public function isTrackable(): bool
    {
        return $this->isTrackable;
    }

    public function setIsTrackable(bool $trackable): void
    {
        $this->isTrackable = $trackable;
    }

    public function setCurrentState(array $currentState): void
    {
        $this->currentState = $currentState;
    }

    public function getCurrentState(): array
    {
        return $this->currentState ?? [];
    }

    /**
     * To string
     * @return string
     */
    public function __toString(): string
    {
        $summary = 'id=%d, name=[%s], status=[%s], exitCode=[%s], exitDescription=[%s]';

        return sprintf(
            $summary,
            $this->id,
            $this->stepName,
            $this->status,
            $this->exitCode,
            $this->exitDescription
        );
    }
}
