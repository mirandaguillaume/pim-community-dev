<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\EventSubscriber;

use Akeneo\Tool\Bundle\ApiBundle\Security\Firewall;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'onKernelRequest')]
final readonly class ValidateApiRequestQueryParametersSubscriber
{
    public function __construct(
        private Firewall $firewall
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->firewall->isCurrentRequestInsideTheApiFirewall()) {
            return;
        }

        $query = $request->query;

        if ($query->count() === 0) {
            return;
        }

        foreach ($query->all() as $parameter) {
            if (is_array($parameter)) {
                throw new BadRequestHttpException('Bracket syntax is not supported in query parameters.');
            }
        }
    }
}
