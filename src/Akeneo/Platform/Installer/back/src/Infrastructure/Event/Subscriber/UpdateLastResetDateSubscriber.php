<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Event\Subscriber;

use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Akeneo\Platform\Installer\Infrastructure\Persistence\Sql\SaveResetEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: InstallerEvents::POST_RESET_INSTANCE, method: 'onInstanceReset')]
class UpdateLastResetDateSubscriber
{
    public function __construct(
        private readonly SaveResetEvent $saveResetEvent,
    ) {
    }

    public function onInstanceReset(): void
    {
        $this->saveResetEvent->withDatetime(new \DateTimeImmutable('now'));
    }
}
