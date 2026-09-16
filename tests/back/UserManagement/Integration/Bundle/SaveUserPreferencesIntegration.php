<?php

declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle;

use Akeneo\Test\Integration\Configuration;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Extension\Acceptor;
use Oro\Bundle\PimDataGridBundle\EventListener\ConfigureProductFiltersListener;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend guard for the user preferences the removed Behat scenario
 * edit_user.feature "Successfully edit and apply user preferences" saved and then applied.
 *
 * The Playwright spec tests/front/e2e/user-management/edit-user.spec.ts still covers the UI flow, but
 * test-playwright does not run on backend-only PRs, and UserControllerEndToEnd (End_to_End suite) is not
 * run by CI. This test runs in test-phpunit-integration, which does run on backend changes. It covers:
 * - POST /rest/user/{identifier} (UserController::postAction -> UpdateUserCommandHandler -> UserUpdater)
 *   persisting the catalog locale, catalog scope, default tree and product grid filters;
 * - the wiring of ConfigureProductFiltersListener on the product-grid build.after event, applied with the
 *   saved preferences to the real product-grid configuration. Its logic alone is unit-tested in
 *   ConfigureProductFiltersListenerTest. The test checks the listener registration and its effect, not that
 *   Builder::build() dispatches the grid-suffixed event (Builder.php:82): dispatching it would also run the
 *   other product-grid listeners, which need a datasource;
 * - the product-grid category tree (ProductGridCategoryTreeController) following the saved catalog locale and
 *   default tree. The front end sends no dataLocale (TreeView.tsx), so the labels come from UserContext's
 *   session then user catalog locale chain, and the selected tree from the user's default tree.
 *
 * Technical catalog: admin starts with en_US / ecommerce / master and no grid filters (users.csv); fr_FR is
 * activated through the tablet channel and master_china is a root category.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SaveUserPreferencesIntegration extends ControllerIntegrationTestCase
{
    private const PREFERENCES = [
        'catalog_default_locale' => 'fr_FR',
        'catalog_default_scope' => 'tablet',
        'default_category_tree' => 'master_china',
        'product_grid_filters' => ['family', 'sku'],
    ];

    public function test_it_saves_the_catalog_preferences_and_the_product_grid_filters_of_the_user(): void
    {
        $admin = $this->findUser('admin');
        // Every preference below really changes.
        Assert::assertSame('en_US', $admin->getCatalogLocale()->getCode());
        Assert::assertSame('ecommerce', $admin->getCatalogScope()->getCode());
        Assert::assertSame('master', $admin->getDefaultTree()->getCode());
        Assert::assertEmpty($admin->getProductGridFilters());

        $this->saveOwnPreferences('admin');

        // Reload from the database, not from the identity map the request may have updated in memory.
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $savedAdmin = $this->findUser('admin');
        Assert::assertSame('fr_FR', $savedAdmin->getCatalogLocale()->getCode());
        Assert::assertSame('tablet', $savedAdmin->getCatalogScope()->getCode());
        Assert::assertSame('master_china', $savedAdmin->getDefaultTree()->getCode());
        Assert::assertSame(['family', 'sku'], $savedAdmin->getProductGridFilters());
    }

    public function test_the_product_grid_enables_only_the_saved_product_grid_filters(): void
    {
        $this->saveOwnPreferences('admin');
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        // The listener reads the user from the token storage, as it does while the product grid is built.
        $this->logIn('admin');

        $listeners = \array_values(\array_filter(
            $this->get('event_dispatcher')->getListeners(BuildAfter::NAME . '.product-grid'),
            static fn ($listener): bool => \is_array($listener) && $listener[0] instanceof ConfigureProductFiltersListener
        ));
        Assert::assertCount(
            1,
            $listeners,
            \sprintf('%s is not registered on %s.product-grid', ConfigureProductFiltersListener::class, BuildAfter::NAME)
        );

        $gridConfiguration = $this->get('oro_datagrid.datagrid.manager')->getConfigurationForGrid('product-grid');
        // A copy: the manager's configuration object is shared with the rest of the kernel.
        $configuration = DatagridConfiguration::create($gridConfiguration->toArray());
        \call_user_func($listeners[0], new BuildAfter(new Datagrid('product-grid', new Acceptor($configuration))));

        // Read the columns array: offsetGetByPath() returns `$value ?: $default`, which turns false into null.
        // datagrid/product.yml sets no `enabled` key on these filters, so null would mean the listener did nothing.
        $columns = $configuration->offsetGetByPath('[filters][columns]');
        Assert::assertTrue($columns['family']['enabled'] ?? null, \json_encode($columns['family'] ?? null));
        Assert::assertFalse($columns['enabled']['enabled'] ?? null, \json_encode($columns['enabled'] ?? null));
        Assert::assertFalse($columns['groups']['enabled'] ?? null, \json_encode($columns['groups'] ?? null));
    }

    public function test_the_product_grid_category_tree_follows_the_saved_catalog_locale_and_default_tree(): void
    {
        // Saved through the updater and saver, not through POST /rest/user/{identifier}. disableReboot() keeps
        // pim_user.context.user alive between requests. On that POST, UserContextListener would memoize the
        // locale in force before the save (en_US) in UserContext::$currentLocale. In production every request
        // gets a fresh container, so the tree requests below must be the first ones to resolve the locale.
        $admin = $this->findUser('admin');
        $this->get('pim_user.updater.user')->update($admin, [
            'catalog_default_locale' => 'fr_FR',
            'default_category_tree' => 'master_china',
        ]);
        $this->get('pim_user.saver.user')->save($admin);
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        // A new session without dataLocale, as after a login (LocaleSubscriber::onSecurityInteractiveLogin).
        $this->logIn('admin');

        $master = $this->get('pim_catalog.repository.category')->findOneByIdentifier('master');
        Assert::assertNotNull($master, 'Category "master" not found');

        // The parameters TreeView.tsx sends, which leaves dataLocale undefined.
        $children = $this->fetchCategoryTree('pim_enrich_product_grid_category_tree_children', [
            'id' => $master->getId(),
            'include_sub' => 0,
            'context' => 'view',
            'with_items_count' => 1,
        ]);
        $categoryA = \current(\array_filter(
            $children,
            static fn (array $child): bool => 'categoryA' === ($child['attr']['data-code'] ?? null)
        ));
        Assert::assertIsArray($categoryA, \json_encode($children));
        // label-fr_FR of categoryA in the technical categories.csv. With en_US it would be "Category A (N)".
        Assert::assertMatchesRegularExpression(
            '/^Catégorie A \(-?\d+\)$/u',
            (string) ($categoryA['data'] ?? ''),
            \json_encode($categoryA)
        );

        // No select_tree_id nor select_node_id: the selected tree is the user's default tree.
        $trees = $this->fetchCategoryTree('pim_enrich_product_grid_category_tree_listtree', [
            'include_sub' => 0,
            'context' => 'view',
        ]);
        $selectedTreeCodes = \array_column(\array_filter(
            $trees,
            static fn (array $tree): bool => 'true' === ($tree['selected'] ?? null)
        ), 'code');
        Assert::assertSame(['master_china'], \array_values($selectedTreeCodes), \json_encode($trees));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return list<array<string, mixed>>
     */
    private function fetchCategoryTree(string $route, array $parameters): array
    {
        $response = $this->callApiRoute($route, ['_format' => 'json'] + $parameters);
        $this->assertStatusCode($response, Response::HTTP_OK);

        $content = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertIsArray($content, (string) $response->getContent());

        return $content;
    }

    private function saveOwnPreferences(string $username): void
    {
        $user = $this->findUser($username);
        $this->logIn($username);

        $response = $this->callApiRoute(
            'pim_user_user_rest_post',
            ['identifier' => (int) $user->getId()],
            Request::METHOD_POST,
            [],
            \json_encode(self::PREFERENCES, JSON_THROW_ON_ERROR)
        );
        $this->assertStatusCode($response, Response::HTTP_OK);

        $content = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        foreach (self::PREFERENCES as $key => $value) {
            Assert::assertSame($value, $content[$key] ?? null, \sprintf('%s: %s', $key, $response->getContent()));
        }
    }

    private function findUser(string $username): UserInterface
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        Assert::assertInstanceOf(UserInterface::class, $user, \sprintf('User "%s" not found', $username));

        return $user;
    }
}
