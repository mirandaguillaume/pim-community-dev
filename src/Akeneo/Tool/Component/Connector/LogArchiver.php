<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Connector;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use League\Flysystem\FilesystemWriter;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Archives logs of the import/export in the "archivist" filesystem.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: EventInterface::BEFORE_JOB_STATUS_UPGRADE, method: 'archive')]
class LogArchiver
{
    public function __construct(private readonly FilesystemWriter $filesystem)
    {
    }

    public function archive(JobExecutionEvent $event): void
    {
        $jobExecution = $event->getJobExecution();
        $logPath = $jobExecution->getLogFile();

        if (is_file($logPath)) {
            $log = fopen($logPath, 'r');
            $this->filesystem->writeStream((string) new LogKey($jobExecution), $log);
            if (is_resource($log)) {
                fclose($log);
            }
        }
    }
}
