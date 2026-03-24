<?php

namespace Akeneo\Platform\Bundle\UIBundle\EventListener;

use Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * User context listener
 * - Define the locale and the scope for the product manager
 * - Define the locale used by the translatable listener
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
#[AsEventListener(event: KernelEvents::REQUEST, method: 'onKernelRequest')]
class UserContextListener
{
    protected \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage;

    protected \Akeneo\Platform\Bundle\UIBundle\EventListener\AddLocaleListener $listener;

    protected \Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext $catalogContext;

    protected \Akeneo\UserManagement\Bundle\Context\UserContext $userContext;

    /**
     * Constructor
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AddLocaleListener $listener,
        CatalogContext $catalogContext,
        UserContext $userContext
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->listener = $listener;
        $this->catalogContext = $catalogContext;
        $this->userContext = $userContext;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernel::MAIN_REQUEST !== $event->getRequestType() || null === $this->tokenStorage->getToken()) {
            return;
        }

        try {
            $this->configureTranslatableListener();
            $this->configureCatalogContext();
        } catch (\LogicException) {
            // If there are no activated locales, skip configuring the listener and productmanager
        }
    }

    /**
     * Configure gedmo translatable locale
     */
    protected function configureTranslatableListener()
    {
        $this->listener->setLocale($this->userContext->getCurrentLocaleCode());
    }

    /**
     * Define locale and scope in CatalogContext
     */
    protected function configureCatalogContext()
    {
        $this->catalogContext->setLocaleCode($this->userContext->getCurrentLocaleCode());
        $this->catalogContext->setScopeCode($this->userContext->getUserChannelCode());
    }
}
