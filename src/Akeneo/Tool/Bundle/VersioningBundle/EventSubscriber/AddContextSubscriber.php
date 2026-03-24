<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext;
use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\JobExecutionEvent;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Add context in version data
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: EventInterface::BEFORE_JOB_EXECUTION, method: 'addContext')]
class AddContextSubscriber
{
    protected \Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext $versionContext;

    /**
     * Constructor
     */
    public function __construct(VersionContext $versionContext)
    {
        $this->versionContext = $versionContext;
    }

    /**
     * Add context in version manager
     */
    public function addContext(JobExecutionEvent $event): void
    {
        $jobInstance = $event->getJobExecution()->getJobInstance();
        if ($jobInstance->getType() === JobInstance::TYPE_IMPORT) {
            $this->versionContext->addContextInfo(
                sprintf('%s "%s"', JobInstance::TYPE_IMPORT, $jobInstance->getCode())
            );
        }
    }
}
