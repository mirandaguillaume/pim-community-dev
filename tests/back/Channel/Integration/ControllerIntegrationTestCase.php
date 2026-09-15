<?php

declare(strict_types=1);

namespace AkeneoTest\Channel\Integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Base class for the Channel HTTP integration tests. A single kernel serves every request of a
 * test, so the test and the controllers share the container, the session and the entity manager.
 *
 * Mirrors tests/back/UserManagement/Integration/Bundle/ControllerIntegrationTestCase.php.
 */
abstract class ControllerIntegrationTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected CatalogInterface $catalog;
    protected RouterInterface $router;

    abstract protected function getConfiguration(): Configuration;

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        $this->router = $this->get('router');
        $this->catalog = $this->get('akeneo_integration_tests.catalogs');
        $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($this->getConfiguration());

        $this->get('akeneo_integration_tests.security.system_user_authenticator')->createSystemUser();
        $this->clearDoctrineUoW();
    }

    protected function tearDown(): void
    {
        $connectionCloser = $this->get('akeneo_integration_tests.doctrine.connection.connection_closer');
        $connectionCloser->closeConnections();

        $this->ensureKernelShutdown();
    }

    protected function get(string $service): mixed
    {
        return self::getContainer()->get($service);
    }

    /**
     * Calls a route the way the UI does: XMLHttpRequest with a JSON body.
     */
    protected function callXhrRoute(
        string $route,
        array $routeArguments = [],
        string $method = 'GET',
        array $parameters = [],
        ?string $content = null
    ): Response {
        return $this->callRoute(
            $route,
            $routeArguments,
            $method,
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'],
            $parameters,
            $content
        );
    }

    protected function callRoute(
        string $route,
        array $routeArguments,
        string $method,
        array $headers,
        array $parameters = [],
        ?string $content = null
    ): Response {
        $url = $this->router->generate($route, $routeArguments);
        $this->client->request($method, $url, $parameters, [], $headers, $content);

        return $this->client->getResponse();
    }

    /**
     * Logs the given user in, creating it with every role and group when it does not exist yet.
     */
    protected function logIn(string $username): void
    {
        $container = $this->client->getContainer();
        $session = $container->has('session.factory')
            ? $container->get('session.factory')->createSession()
            : $container->get('session');

        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        if (null === $user) {
            $user = $this->createUserWithRoles($username);
        }

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        $session->set('_security_main', serialize($token));
        $session->save();

        $this->client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
    }

    /**
     * @param string[]|null $roleCodes the roles to grant, or null for every role
     */
    protected function createUserWithRoles(string $username, ?array $roleCodes = null): UserInterface
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setId(uniqid());
        $user->setUsername($username);
        $user->setEmail(sprintf('%s@example.com', uniqid()));
        $user->setPlainPassword('fake');
        $this->get('pim_user.manager')->updatePassword($user);

        foreach ($this->get('pim_user.repository.group')->findAll() as $group) {
            $user->addGroup($group);
        }

        foreach ($this->get('pim_user.repository.role')->findAll() as $role) {
            if (null === $roleCodes || in_array($role->getRole(), $roleCodes, true)) {
                $user->addRole($role);
            }
        }

        $this->get('pim_user.saver.user')->save($user);

        return $user;
    }

    /**
     * Same approach as tests/back/UserManagement/Integration/Bundle/DuplicateUserIntegration.php.
     */
    protected function revokePermissionFromRole(string $roleCode, string $aclId): void
    {
        $role = $this->get('pim_user.repository.role')->findOneByIdentifier($roleCode);
        Assert::assertNotNull($role);

        $aclManager = $this->get('oro_security.acl.manager');
        $sid = $aclManager->getSid($role);
        foreach ($aclManager->getAllExtensions() as $extension) {
            foreach ($extension->getClasses() as $aclClassInfo) {
                if ($aclClassInfo->getClassName() === $aclId) {
                    $oid = new ObjectIdentity($extension->getExtensionKey(), $aclClassInfo->getClassName());
                    $aclManager->setPermission($sid, $oid, AccessLevel::NONE_LEVEL, true);
                }
            }
        }

        $aclManager->flush();
    }

    protected function clearDoctrineUoW(): void
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function decodeJson(Response $response): array
    {
        return json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    protected function assertStatusCode(Response $response, int $statusCode): void
    {
        Assert::assertSame($statusCode, $response->getStatusCode(), sprintf(
            'Expected response status code is not the same as the actual. Failed with content %s',
            $response->getContent()
        ));
    }
}
