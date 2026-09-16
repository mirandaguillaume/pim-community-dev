<?php

declare(strict_types=1);

/**
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Controller\InternalApi;

use Akeneo\Category\Application\Storage\Save\Saver\CategoryBaseSaver;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Test\IntegrationTestsBundle\Configuration\Catalog;
use Akeneo\Test\IntegrationTestsBundle\Doctrine\Connection\ConnectionCloser;
use Akeneo\Test\IntegrationTestsBundle\Helper\AuthenticatorHelper;
use Akeneo\Test\IntegrationTestsBundle\Loader\FixturesLoader;
use Akeneo\Test\IntegrationTestsBundle\Security\SystemUserAuthenticator;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * Runs the category label edit of the removed Behat scenario edit_a_category.feature:11 through the internal API, with
 * the container-wired converter, ACL filters, command bus and savers behind UpdateCategoryController.
 *
 * Ported from the labels and not-found tests of UpdateCategoryControllerEndToEnd, which only the Category_EndToEnd_Test
 * suite ran, and no CI job runs that suite.
 */
class UpdateCategoryControllerIntegration extends WebTestCase
{
    private const string EDIT_ACL = 'pim_enrich_product_category_edit';

    private KernelBrowser $client;
    private int $categoryId;

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        $this->service('akeneo_integration_tests.loader.fixtures_loader', FixturesLoader::class)
            ->load($this->service('akeneo_integration_tests.catalogs', Catalog::class)->useMinimalCatalog());
        $this->service('akeneo_integration_tests.security.system_user_authenticator', SystemUserAuthenticator::class)
            ->createSystemUser();
        $this->service('pim_connector.doctrine.cache_clearer', UnitOfWorkAndRepositoriesClearer::class)->clear();

        $this->categoryId = $this->createCategory('jeans', 'master');
    }

    protected function tearDown(): void
    {
        $this->service('akeneo_integration_tests.doctrine.connection.connection_closer', ConnectionCloser::class)
            ->closeConnections();

        parent::tearDown();
    }

    public function testItUpdatesTheCategoryLabels(): void
    {
        $this->assertSame([], $this->labelsOfTheCategory());
        $newLabels = ['de_DE' => 'Hose', 'en_US' => 'Pants', 'fr_FR' => 'Pantalon'];

        $this->logAs('julia');
        $response = $this->postLabels($newLabels);

        $this->assertStatusCode(Response::HTTP_OK, $response);
        $body = $this->decode($response);
        $this->assertTrue($this->valueAt($body, 'success'));
        $this->assertSame($this->categoryId, $this->valueAt($body, 'category', 'id'));
        $this->assertSame('jeans', $this->valueAt($body, 'category', 'properties', 'code'));
        $this->assertEquals($newLabels, $this->valueAt($body, 'category', 'properties', 'labels'));

        $this->assertEquals($newLabels, $this->labelsOfTheCategory());
        $this->assertSame('jeans', (string) $this->theCategory()->getCode());

        // The edit form reloads the category through the GET route.
        $response = $this->callApiRoute('pim_enriched_category_rest_get', ['id' => (string) $this->categoryId], Request::METHOD_GET);
        $this->assertStatusCode(Response::HTTP_OK, $response);
        $body = $this->decode($response);
        $this->assertSame('jeans', $this->valueAt($body, 'properties', 'code'));
        $this->assertEquals($newLabels, $this->valueAt($body, 'properties', 'labels'));
    }

    public function testItForbidsTheUpdateWithoutTheCategoryEditPermission(): void
    {
        $this->revokeTheCategoryEditPermissionFromEveryRole();

        $this->logAs('julia');
        $response = $this->postLabels(['en_US' => 'Pants']);

        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $response);
        $this->assertSame([], $this->labelsOfTheCategory());
    }

    public function testItReturnsNotFoundForAnUnknownCategory(): void
    {
        $this->logAs('julia');
        $response = $this->callApiRoute('pim_enriched_category_rest_update', ['id' => '999999999'], Request::METHOD_POST);

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $response);
    }

    /**
     * @param array<string, string> $labels
     */
    private function postLabels(array $labels): Response
    {
        $category = $this->theCategory();

        // The payload the category edit form sends.
        return $this->callApiRoute(
            'pim_enriched_category_rest_update',
            ['id' => (string) $this->categoryId],
            Request::METHOD_POST,
            json_encode([
                'id' => $this->categoryId,
                'parent' => $category->getParentId()?->getValue(),
                'root_id' => $category->getRootId()?->getValue(),
                'template_uuid' => null,
                'properties' => [
                    'code' => (string) $category->getCode(),
                    'labels' => $labels,
                ],
                'attributes' => [],
                'permissions' => [],
                'isRoot' => $category->isRoot(),
            ], JSON_THROW_ON_ERROR),
        );
    }

    private function createCategory(string $code, string $parentCode): int
    {
        $parent = $this->service(GetCategoryInterface::class, GetCategoryInterface::class)->byCode($parentCode);
        if (!$parent instanceof Category) {
            throw new \LogicException(sprintf('The category "%s" does not exist.', $parentCode));
        }

        $this->service(CategoryBaseSaver::class, CategoryBaseSaver::class)->save(new Category(
            id: null,
            code: new Code($code),
            templateUuid: null,
            parentId: $parent->getId(),
            parentCode: new Code($parentCode),
            rootId: $parent->getId(),
        ));

        $categoryId = $this->service(GetCategoryInterface::class, GetCategoryInterface::class)->byCode($code)?->getId();
        if (null === $categoryId) {
            throw new \LogicException(sprintf('The category "%s" was not created.', $code));
        }

        return $categoryId->getValue();
    }

    private function theCategory(): Category
    {
        $category = $this->service(GetCategoryInterface::class, GetCategoryInterface::class)->byId($this->categoryId);
        if (!$category instanceof Category) {
            throw new \LogicException('The category of the test does not exist.');
        }

        return $category;
    }

    /**
     * @return array<string, string>
     */
    private function labelsOfTheCategory(): array
    {
        return $this->theCategory()->getLabels()?->getTranslations() ?? [];
    }

    /**
     * Same approach as tests/back/UserManagement/Integration/Bundle/DuplicateUserIntegration.php. The logged-in user
     * gets every role, so the permission is revoked from all of them.
     */
    private function revokeTheCategoryEditPermissionFromEveryRole(): void
    {
        $aclManager = $this->service('oro_security.acl.manager', AclManager::class);
        $roles = $this->service('pim_user.repository.role', ObjectRepository::class)->findAll();
        $this->assertNotEmpty($roles);

        foreach ($roles as $role) {
            if (!$role instanceof RoleInterface) {
                continue;
            }
            $sid = $aclManager->getSid($role);
            foreach ($aclManager->getAllExtensions() as $extension) {
                foreach ($extension->getClasses() as $aclClassInfo) {
                    if (self::EDIT_ACL === $aclClassInfo->getClassName()) {
                        $oid = new ObjectIdentity($extension->getExtensionKey(), $aclClassInfo->getClassName());
                        $aclManager->setPermission($sid, $oid, AccessLevel::NONE_LEVEL, true);
                    }
                }
            }
        }

        $aclManager->flush();
    }

    private function logAs(string $username): void
    {
        $this->service('akeneo_integration_tests.helper.authenticator', AuthenticatorHelper::class)
            ->logIn($username, $this->client);
    }

    /**
     * @param array<string, string> $routeArguments
     */
    private function callApiRoute(string $route, array $routeArguments, string $method, ?string $content = null): Response
    {
        $this->client->request(
            $method,
            $this->service('router', RouterInterface::class)->generate($route, $routeArguments),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'],
            $content,
        );

        return $this->client->getResponse();
    }

    /**
     * @return array<mixed>
     */
    private function decode(Response $response): array
    {
        $decoded = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($decoded)) {
            throw new \LogicException(sprintf('The response is not a JSON object: %s', $response->getContent()));
        }

        return $decoded;
    }

    /**
     * @param array<mixed> $data
     */
    private function valueAt(array $data, string ...$path): mixed
    {
        $value = $data;
        foreach ($path as $key) {
            if (!\is_array($value) || !\array_key_exists($key, $value)) {
                $this->fail(sprintf('No "%s" in the response: %s', implode('.', $path), json_encode($data)));
            }
            $value = $value[$key];
        }

        return $value;
    }

    private function assertStatusCode(int $expected, Response $response): void
    {
        $this->assertSame(
            $expected,
            $response->getStatusCode(),
            sprintf('Unexpected status code, content: %s', $response->getContent()),
        );
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private function service(string $id, string $class): object
    {
        $service = self::getContainer()->get($id);
        if (!$service instanceof $class) {
            throw new \LogicException(sprintf('The service "%s" is not a %s.', $id, $class));
        }

        return $service;
    }
}
