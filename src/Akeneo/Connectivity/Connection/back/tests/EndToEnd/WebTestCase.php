<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class WebTestCase extends TestCase
{
    /** @var KernelBrowser */
    protected $client;

    private ?SessionInterface $session = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::getContainer()->get('test.client');
    }

    protected function createConnection(string $code, string $label, string $flowType, bool $auditable): ConnectionWithCredentials
    {
        $createConnectionCommand = new CreateConnectionCommand($code, $label, $flowType, $auditable);

        return $this
            ->get(CreateConnectionHandler::class)
            ->handle($createConnectionCommand);
    }

    protected function authenticateAsAdmin(): UserInterface
    {
        $user = $this->createAdminUser();

        $this->authenticate($user);

        return $user;
    }

    private function authenticate(UserInterface $user): void
    {
        $firewallName = 'main';
        $firewallContext = 'main';

        $token = new UsernamePasswordToken($user, $firewallName, $user->getRoles());
        // Reuse the session an earlier arrangement may already have opened, so state put
        // there by inAuthenticatedSession() survives logging in.
        $session = $this->session ?? $this->getSession();
        $this->session = $session;
        $session->set('_security_' . $firewallContext, \serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    private function getSession(): SessionInterface
    {
        $container = $this->client->getContainer();

        if ($container->has('session.factory')) {
            /** @var \Symfony\Component\HttpFoundation\Session\SessionFactoryInterface $sessionFactory */
            $sessionFactory = $container->get('session.factory');

            return $sessionFactory->createSession();
        }

        /** @var SessionInterface $session */
        $session = $container->get('session');

        return $session;
    }

    /**
     * The app activation wizard keeps its state in the session. A test that arranges that
     * state by calling a handler directly runs outside the kernel, where the RequestStack
     * holds no request and therefore no session, so the call throws SessionNotFoundException.
     *
     * Run the arrangement inside a request carrying the very session the client sends back,
     * then persist it, so the endpoint under test reads exactly what was arranged.
     */
    protected function inAuthenticatedSession(callable $arrange): void
    {
        $session = $this->session;
        if (null === $session) {
            $session = $this->getSession();
            $this->session = $session;
            $this->client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
        }

        $request = new Request();
        $request->setSession($session);

        /** @var RequestStack $requestStack */
        $requestStack = static::getContainer()->get('request_stack');
        $requestStack->push($request);

        try {
            $arrange();
        } finally {
            $session->save();
            $requestStack->pop();
        }
    }

    /**
     * Read back the session the client sends with its requests, as storage holds it now.
     * An endpoint writes into the session it loaded from the cookie and saves it there, so a
     * test holding a Session object built before the request would not see those writes: the
     * same id has to be loaded again.
     */
    protected function reloadAuthenticatedSession(): SessionInterface
    {
        $session = $this->getSession();
        $session->setId($this->session?->getId() ?? '');
        $session->start();

        return $session;
    }

    protected function addAclToRole(string $roleCode, string $acl): void
    {
        $this->changeAclInRole($roleCode, $acl, true);
    }

    protected function removeAclFromRole(string $roleCode, string $acl): void
    {
        $this->changeAclInRole($roleCode, $acl, false);
    }

    private function changeAclInRole(string $roleCode, string $acl, bool $enabled): void
    {
        /** @var AclManager $aclManager */
        $aclManager = $this->get('oro_security.acl.manager');
        /** @var RoleWithPermissionsRepository $roleWithPermissionsRepository */
        $roleWithPermissionsRepository = $this->get('pim_user.repository.role_with_permissions');
        /** @var RoleWithPermissionsSaver $roleWithPermissionsSaver */
        $roleWithPermissionsSaver = $this->get('pim_user.saver.role_with_permissions');

        $roleWithPermissions = $roleWithPermissionsRepository->findOneByIdentifier($roleCode);
        \assert(null !== $roleWithPermissions);

        $permissions = $roleWithPermissions->permissions();
        $permissions[\sprintf('action:%s', $acl)] = $enabled;
        $roleWithPermissions->setPermissions($permissions);

        $roleWithPermissionsSaver->saveAll([$roleWithPermissions]);

        $aclManager->flush();
        $aclManager->clearCache();
    }
}
