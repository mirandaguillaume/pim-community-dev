<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Controller;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * Backend guard of the category tree grid, whose "Delete tree" UI flow is covered by
 * tests/front/e2e/critical/category.spec.ts (formerly Behat remove_a_category.feature:22). The grid lists the trees
 * through pim_enrich_categorytree_listtree and deletes one through pim_enrich_categorytree_remove
 * (CategoryTreeController::removeAction), an XHR-only route answering 204.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTreeControllerIntegration extends WebTestCase
{
    protected KernelBrowser $client;

    public function test_it_removes_a_category_tree_through_the_grid_routes(): void
    {
        $treeId = $this->createCategory(['code' => 'tree_to_remove', 'labels' => ['en_US' => 'Tree to remove']]);
        $this->logIn('admin');
        Assert::assertContains(
            ['code' => 'tree_to_remove', 'label' => 'Tree to remove'],
            $this->listTrees(),
            'the grid must list the new tree with its label'
        );

        $this->client->request(
            Request::METHOD_DELETE,
            $this->getRouter()->generate('pim_enrich_categorytree_remove', ['id' => $treeId]),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode(), (string) $response->getContent());
        Assert::assertNull($this->findCategory('tree_to_remove'), 'the tree must be deleted');
        Assert::assertNotNull($this->findCategory('master'), 'the other trees must be kept');
        $listedCodes = array_column($this->listTrees(), 'code');
        Assert::assertNotContains('tree_to_remove', $listedCodes);
        Assert::assertContains('master', $listedCodes);
    }

    public function test_it_does_not_remove_a_category_tree_on_a_non_xhr_request(): void
    {
        $treeId = $this->createCategory(['code' => 'tree_to_keep']);
        $this->logIn('admin');

        $this->client->request(
            Request::METHOD_DELETE,
            $this->getRouter()->generate('pim_enrich_categorytree_remove', ['id' => $treeId])
        );

        Assert::assertTrue($this->client->getResponse()->isRedirect('/'), 'a non-XHR request must be redirected');
        Assert::assertNotNull($this->findCategory('tree_to_keep'), 'a non-XHR request must not delete the tree');
    }

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($this->get('akeneo_integration_tests.catalogs')->useTechnicalCatalog());

        $this->get('akeneo_integration_tests.security.system_user_authenticator')->createSystemUser();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function get(string $service)
    {
        return self::getContainer()->get($service);
    }

    private function createCategory(array $data): int
    {
        $category = $this->get('pim_catalog.factory.category')->create();
        $this->get('pim_catalog.updater.category')->update($category, $data);
        $violations = $this->get('validator')->validate($category);
        Assert::assertCount(0, $violations, (string) $violations);
        $this->get('pim_catalog.saver.category')->save($category);

        return $category->getId();
    }

    private function findCategory(string $code): ?object
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        return $this->get('pim_catalog.repository.category')->findOneByIdentifier($code);
    }

    /**
     * @return array<array{code: string, label: string}> the trees as the category grid lists them
     */
    private function listTrees(): array
    {
        $this->client->request(
            Request::METHOD_GET,
            $this->getRouter()->generate('pim_enrich_categorytree_listtree', [
                '_format' => 'json',
                'context' => 'manage',
                'include_sub' => '0',
                'with_items_count' => '0',
            ]),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());

        return array_map(
            fn(array $tree): array => ['code' => $tree['code'], 'label' => $tree['label']],
            \json_decode((string) $response->getContent(), true)
        );
    }

    private function logIn(string $username): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($username, $this->client);
    }

    private function getRouter(): RouterInterface
    {
        return $this->get('router');
    }
}
