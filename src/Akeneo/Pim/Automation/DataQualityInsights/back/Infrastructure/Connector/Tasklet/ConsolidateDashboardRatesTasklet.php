<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateDashboardRates;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\PeriodicTasksParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConsolidateDashboardRatesTasklet implements TaskletInterface
{
    private ?\Akeneo\Tool\Component\Batch\Model\StepExecution $stepExecution = null;

    public function __construct(private readonly ConsolidateDashboardRates $consolidateDashboardRates, private readonly LoggerInterface $logger)
    {
    }

    /**
     * @inheritDoc
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $jobParameters = $this->stepExecution->getJobParameters();
            $date = \DateTimeImmutable::createFromFormat(PeriodicTasksParameters::DATE_FORMAT, $jobParameters->get(PeriodicTasksParameters::DATE_FIELD));

            $this->consolidateDashboardRates->consolidate(new ConsolidationDate($date));
        } catch (\Exception $exception) {
            $this->stepExecution->addFailureException($exception);
            $this->logger->error('Consolidate Data-Quality-Insights dashboard rates failed', [
                'step_execution_id' => $this->stepExecution->getId(),
                'message' => $exception->getMessage()
            ]);
        }
    }
}
