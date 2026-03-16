<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\EventSubscriber;

use Akeneo\Platform\Installer\Application\IsMaintenanceModeEnabled\IsMaintenanceModeEnabledHandler;
use Akeneo\Platform\Installer\Application\UpdateMaintenanceMode\UpdateMaintenanceModeCommand;
use Akeneo\Platform\Installer\Application\UpdateMaintenanceMode\UpdateMaintenanceModeHandler;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'redirectToMaintenanceLandingPage')]
#[AsEventListener(event: InstallerEvents::PRE_RESET_INSTANCE, method: 'enableMaintenanceMode')]
#[AsEventListener(event: InstallerEvents::POST_RESET_INSTANCE, method: 'disableMaintenanceMode')]
class MaintenanceModeSubscriber
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly IsMaintenanceModeEnabledHandler $isMaintenanceModeEnabledHandler,
        private readonly UpdateMaintenanceModeHandler $updateMaintenanceModeHandler,
    ) {
    }

    private const EXCLUDED_ROUTES = [
        'akeneo_installer_maintenance_page',
        'akeneo_installer_is_maintenance_mode_enabled',
    ];

    public function redirectToMaintenanceLandingPage(RequestEvent $event): void
    {
        if (\in_array($event->getRequest()->attributes->get('_route'), self::EXCLUDED_ROUTES, true)) {
            return;
        }

        if ($this->isMaintenanceModeEnabledHandler->handle()) {
            $this->setEventResponse($event);
        }
    }

    private function setEventResponse(RequestEvent $event): void
    {
        if ($this->isApiRequest($event->getRequest())) {
            $event->setResponse(
                new Response(
                    'Undergoing maintenance.',
                    Response::HTTP_SERVICE_UNAVAILABLE,
                ),
            );

            return;
        }

        $event->setResponse(new RedirectResponse($this->router->generate('akeneo_installer_maintenance_page')));
    }

    public function enableMaintenanceMode(): void
    {
        $this->updateMaintenanceModeHandler->handle(
            new UpdateMaintenanceModeCommand(true),
        );
    }

    public function disableMaintenanceMode(): void
    {
        $this->updateMaintenanceModeHandler->handle(
            new UpdateMaintenanceModeCommand(false),
        );
    }

    private function isApiRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api/');
    }
}
