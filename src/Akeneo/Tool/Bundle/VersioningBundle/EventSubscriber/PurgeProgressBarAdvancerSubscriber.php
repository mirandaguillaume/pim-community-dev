<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Tool\Bundle\VersioningBundle\Event\PreAdvisementVersionEvent;
use Akeneo\Tool\Bundle\VersioningBundle\Event\PurgeVersionEvents;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @deprecated Will be removed in 4.0
 *
 * @todo merge in master: remove this class
 *
 * Subscriber that advances a progress bar during a purge version operation
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: PurgeVersionEvents::PRE_ADVISEMENT, method: 'advanceProgressbar')]
class PurgeProgressBarAdvancerSubscriber
{
    /** @var ProgressBar */
    protected $progressBar;

    /**
     * Keeps the progress bar in track with the processed versions
     */
    public function advanceProgressBar(PreAdvisementVersionEvent $preAdvisementVersionEvent): void
    {
        if (null !== $this->progressBar) {
            $this->progressBar->advance();
        }
    }

    public function setProgressBar(ProgressBar $progressBar): void
    {
        $this->progressBar = $progressBar;
    }
}
