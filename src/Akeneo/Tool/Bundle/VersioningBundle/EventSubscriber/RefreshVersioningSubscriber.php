<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Akeneo\Tool\Bundle\BatchBundle\JobExecution\CreateJobExecutionHandlerInterface;
use Akeneo\Tool\Bundle\BatchBundle\JobExecution\ExecuteJobExecutionHandlerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
#[AsEventListener(event: InstallerEvents::POST_LOAD_FIXTURES, method: 'refreshVersioning', priority: 200)]
class RefreshVersioningSubscriber
{
    private const JOB_CODE = 'versioning_refresh';

    public function __construct(
        private readonly ExecuteJobExecutionHandlerInterface $jobExecutionRunner,
        private readonly CreateJobExecutionHandlerInterface $jobExecutionFactory,
    ) {
    }

    public function refreshVersioning(): void
    {
        $jobExecution = $this->jobExecutionFactory->createFromBatchCode(self::JOB_CODE, [], null);
        $this->jobExecutionRunner->executeFromJobExecutionId($jobExecution->getId());
    }
}
