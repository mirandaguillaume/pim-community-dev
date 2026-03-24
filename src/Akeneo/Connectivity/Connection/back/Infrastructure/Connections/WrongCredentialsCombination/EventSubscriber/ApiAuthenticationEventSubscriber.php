<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Connections\WrongCredentialsCombination\EventSubscriber;

use Akeneo\Connectivity\Connection\Application\ConnectionContextInterface;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Write\WrongCredentialsCombination;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Repository\WrongCredentialsCombinationRepositoryInterface;
use Akeneo\Tool\Component\Api\Event\ApiAuthenticationEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: ApiAuthenticationEvent::class, method: 'checkCredentialsCombination')]
class ApiAuthenticationEventSubscriber
{
    public function __construct(
        private readonly ConnectionContextInterface $connectionContext,
        private readonly WrongCredentialsCombinationRepositoryInterface $repository,
    ) {
    }

    public function checkCredentialsCombination(ApiAuthenticationEvent $event): void
    {
        if ($this->connectionContext->areCredentialsValidCombination()) {
            return;
        }

        if (!$this->connectionContext->getConnection() instanceof \Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection) {
            return;
        }

        $this->repository->create(new WrongCredentialsCombination(
            (string) $this->connectionContext->getConnection()->code(),
            $event->username()
        ));
    }
}
