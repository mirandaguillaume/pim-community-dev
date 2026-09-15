<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Controller\InternalApi;

use Akeneo\Test\Integration\Configuration;
use Oro\Bundle\PimDataGridBundle\tests\Integration\Controller\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend guard of the product grid category tree panel, whose UI flow is covered by
 * tests/front/e2e/product/filter-products-per-category.spec.ts (formerly the @critical Behat scenario
 * filter_products_per_category.feature:19). The panel lists the trees through
 * pim_enrich_product_grid_category_tree_listtree and expands a node through
 * pim_enrich_product_grid_category_tree_children (ProductGridCategoryTreeController); both answer "Label (count)"
 * labels whose count depends on the "include sub-categories" switch.
 *
 * Fixture: grid_tree > grid_men > grid_men_summer and grid_tree > grid_women, with one product in grid_men_summer,
 * one in grid_women and one unclassified product.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductGridCategoryTreeControllerIntegration extends ControllerIntegrationTestCase
{
    private const string RESTRICTED_ROLE = 'ROLE_WITHOUT_CATEGORY_LIST';
    private const string CATEGORY_LIST_PERMISSION = 'action:pim_enrich_product_category_list';

    protected function setUp(): void
    {
        parent::setUp();

        $fixturesLoader = $this->get('akeneo_integration_tests.loader.category_tree_loader');
        $fixturesLoader->givenTheCategoryTrees([
            'grid_tree' => [
                'grid_men' => [
                    'grid_men_summer' => [],
                ],
                'grid_women' => [],
            ],
        ]);
        $fixturesLoader->givenTheProductsWithCategories([
            'grid_men_summer_product' => ['grid_men_summer'],
            'grid_women_product' => ['grid_women'],
            'grid_unclassified_product' => [],
        ]);
        $this->get('pim_catalog.validator.unique_value_set')->reset();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    public function test_it_lists_the_trees_with_the_count_of_the_selected_tree_including_or_not_its_sub_categories(): void
    {
        $this->logIn('admin');
        $treeId = $this->categoryId('grid_tree');

        $trees = $this->listTrees(['select_tree_id' => $treeId, 'include_sub' => '1']);
        Assert::assertSame(
            ['id' => $treeId, 'code' => 'grid_tree', 'label' => 'Grid_tree (2)', 'selected' => 'true'],
            $this->findByKey($trees, 'code', 'grid_tree'),
            'the count including sub-categories covers the 2 classified products, not the unclassified one'
        );
        Assert::assertSame('false', $this->findByKey($trees, 'code', 'master')['selected']);

        $trees = $this->listTrees(['select_tree_id' => $treeId, 'include_sub' => '0']);
        Assert::assertSame(
            'Grid_tree (0)',
            $this->findByKey($trees, 'code', 'grid_tree')['label'],
            'no product is classified directly in the tree root'
        );
    }

    public function test_it_selects_the_tree_of_the_category_selected_as_filter(): void
    {
        $this->logIn('admin');

        $trees = $this->listTrees(['select_node_id' => $this->categoryId('grid_men_summer'), 'include_sub' => '1']);

        Assert::assertSame('true', $this->findByKey($trees, 'code', 'grid_tree')['selected']);
        Assert::assertSame('false', $this->findByKey($trees, 'code', 'master')['selected']);
    }

    public function test_it_lists_the_children_with_their_count_including_or_not_their_sub_categories(): void
    {
        $this->logIn('admin');
        $treeId = $this->categoryId('grid_tree');
        $menId = $this->categoryId('grid_men');
        $womenId = $this->categoryId('grid_women');

        $children = $this->listChildren(['id' => $treeId, 'include_sub' => '0']);
        Assert::assertSame(
            [
                'attr' => ['id' => 'node_' . $menId, 'data-code' => 'grid_men'],
                'data' => 'Grid_men (0)',
                'state' => 'closed',
                'children' => [],
            ],
            $this->findChild($children, 'grid_men')
        );
        Assert::assertSame(
            [
                'attr' => ['id' => 'node_' . $womenId, 'data-code' => 'grid_women'],
                'data' => 'Grid_women (1)',
                'state' => 'leaf',
                'children' => [],
            ],
            $this->findChild($children, 'grid_women')
        );

        $children = $this->listChildren(['id' => $treeId, 'include_sub' => '1']);
        Assert::assertSame(
            'Grid_men (1)',
            $this->findChild($children, 'grid_men')['data'],
            'the count including sub-categories covers the grid_men_summer product'
        );
        Assert::assertSame('Grid_women (1)', $this->findChild($children, 'grid_women')['data']);
    }

    public function test_it_expands_the_children_down_to_the_category_selected_as_filter(): void
    {
        $this->logIn('admin');
        $menSummerId = $this->categoryId('grid_men_summer');

        $children = $this->listChildren([
            'id' => $this->categoryId('grid_tree'),
            'select_node_id' => $menSummerId,
            'include_sub' => '1',
        ]);

        Assert::assertSame(
            [
                'attr' => ['id' => 'node_' . $this->categoryId('grid_men'), 'data-code' => 'grid_men'],
                'data' => 'Grid_men (1)',
                'state' => 'open',
                'children' => [
                    [
                        'attr' => ['id' => 'node_' . $menSummerId, 'data-code' => 'grid_men_summer'],
                        'data' => 'Grid_men_summer (1)',
                        'state' => 'leaf toselect',
                        'children' => [],
                    ],
                ],
            ],
            $this->findChild($children, 'grid_men')
        );
    }

    public function test_it_denies_the_tree_and_the_children_to_a_user_without_the_category_list_permission(): void
    {
        $this->createUserWithoutTheCategoryListPermission('no_category_list');
        $this->logIn('no_category_list');
        $treeId = $this->categoryId('grid_tree');

        $this->callApiRoute(
            $this->client,
            'pim_enrich_product_grid_category_tree_listtree',
            ['_format' => 'json', 'select_tree_id' => $treeId, 'include_sub' => '1'],
            Request::METHOD_GET
        );
        $this->assertStatusCode($this->client->getResponse(), Response::HTTP_FORBIDDEN);

        $this->callApiRoute(
            $this->client,
            'pim_enrich_product_grid_category_tree_children',
            ['_format' => 'json', 'id' => $treeId, 'include_sub' => '1'],
            Request::METHOD_GET
        );
        $this->assertStatusCode($this->client->getResponse(), Response::HTTP_FORBIDDEN);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param array<string, int|string> $queryParameters
     *
     * @return list<array{id: int, code: string, label: string, selected: string}>
     */
    private function listTrees(array $queryParameters): array
    {
        return $this->getJson('pim_enrich_product_grid_category_tree_listtree', $queryParameters);
    }

    /**
     * @param array<string, int|string> $queryParameters
     *
     * @return list<array<string, mixed>>
     */
    private function listChildren(array $queryParameters): array
    {
        return $this->getJson('pim_enrich_product_grid_category_tree_children', $queryParameters);
    }

    /**
     * @param array<string, int|string> $queryParameters
     */
    private function getJson(string $route, array $queryParameters): array
    {
        $this->callApiRoute($this->client, $route, ['_format' => 'json'] + $queryParameters, Request::METHOD_GET);
        $response = $this->client->getResponse();
        $this->assertStatusCode($response, Response::HTTP_OK);

        $content = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertIsArray($content, (string) $response->getContent());

        return $content;
    }

    /**
     * @param list<array<string, mixed>> $children
     *
     * @return array<string, mixed>
     */
    private function findChild(array $children, string $code): array
    {
        foreach ($children as $child) {
            if (($child['attr']['data-code'] ?? null) === $code) {
                return $child;
            }
        }

        Assert::fail(sprintf('The child category "%s" is not listed in %s', $code, \json_encode($children)));
    }

    /**
     * @param list<array<string, mixed>> $items
     *
     * @return array<string, mixed>
     */
    private function findByKey(array $items, string $key, string $value): array
    {
        foreach ($items as $item) {
            if (($item[$key] ?? null) === $value) {
                return $item;
            }
        }

        Assert::fail(sprintf('No item with %s "%s" in %s', $key, $value, \json_encode($items)));
    }

    private function categoryId(string $code): int
    {
        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier($code);
        Assert::assertNotNull($category, sprintf('The category "%s" must exist', $code));

        return $category->getId();
    }

    private function createUserWithoutTheCategoryListPermission(string $username): void
    {
        $role = $this->get('pim_user.factory.role')->create();
        $role->setRole(self::RESTRICTED_ROLE);
        $role->setLabel('Without category list');
        $this->get('pim_user.saver.role')->save($role);

        // Same permissions as the administrator, except the one checked by ProductGridCategoryTreeController.
        $roleWithPermissionsRepository = $this->get('pim_user.repository.role_with_permissions');
        $permissions = $roleWithPermissionsRepository->findOneByIdentifier('ROLE_ADMINISTRATOR')->permissions();
        Assert::assertTrue(
            $permissions[self::CATEGORY_LIST_PERMISSION] ?? false,
            'the administrator must be granted the category list permission'
        );
        $permissions[self::CATEGORY_LIST_PERMISSION] = false;
        $restrictedRole = $roleWithPermissionsRepository->findOneByIdentifier(self::RESTRICTED_ROLE);
        $restrictedRole->setPermissions($permissions);
        $this->get('pim_user.saver.role_with_permissions')->saveAll([$restrictedRole]);

        $aclManager = $this->get('oro_security.acl.manager');
        $aclManager->flush();
        $aclManager->clearCache();

        $user = $this->get('pim_user.factory.user')->create();
        $user->setId(uniqid());
        $user->setUsername($username);
        $user->setEmail(sprintf('%s@example.com', uniqid()));
        $user->setPassword('fake');
        foreach ($this->get('pim_user.repository.group')->findAll() as $group) {
            $user->addGroup($group);
        }
        $user->addRole($this->get('pim_user.repository.role')->findOneByIdentifier(self::RESTRICTED_ROLE));
        $this->get('pim_user.saver.user')->save($user);

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }
}
