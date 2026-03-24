<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\EventSubscriber;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\IsAccessTokenRevokedQueryInterface;
use Akeneo\Tool\Component\Api\Event\ApiAuthenticationFailedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: ApiAuthenticationFailedEvent::class, method: 'throwIfDeniedAccessTokenIsRevoked')]
class AccessDeniedForRevokedAppTokenEventSubscriber
{
    private const string MESSAGE = 'The access token provided is invalid. Your app has been disconnected from that PIM.';

    public function __construct(
        private readonly IsAccessTokenRevokedQueryInterface $isAccessTokenRevokedQuery,
    ) {
    }

    public function throwIfDeniedAccessTokenIsRevoked(ApiAuthenticationFailedEvent $event): void
    {
        $token = $event->getToken();
        $isRevoked = $this->isAccessTokenRevokedQuery->execute($token);

        if ($isRevoked) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, self::MESSAGE, $event->getException());
        }
    }
}
