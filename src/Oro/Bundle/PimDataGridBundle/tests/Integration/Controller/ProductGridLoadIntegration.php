<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\tests\Integration\Controller;

use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend guard of the product grid load (pim_datagrid_load alias=product-grid), whose UI flows are covered by
 * tests/front/e2e/product/filter-products-per-category.spec.ts and datagrid-views.spec.ts (formerly Behat
 * filter_products_per_category.feature:19 and datagrid_views.feature:17). It builds the real product-grid
 * configuration (Enrichment datagrid/product.yml plus the Data Quality Insights one) through the build.before
 * listeners, and filters the rows with the category filter the tree panel sends.
 *
 * The request mirrors the GET callers of the route: dataLocale in "params" and the grid state under "product-grid".
 * The feature flags of the test environment are file persisted, so every test sets data_quality_insights explicitly
 * and tearDown deletes the file.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductGridLoadIntegration extends ControllerIntegrationTestCase
{
    private const array DEFAULT_COLUMNS = [
        'identifier',
        'image',
        'label',
        'family',
        'enabled',
        'completeness',
        'created',
        'updated',
        'complete_variant_products',
    ];

    private const array QUALITY_FILTERS = [
        'data_quality_insights_score',
        'data_quality_insights_enrichment_quality',
        'data_quality_insights_images_quality',
    ];

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

        $this->logIn('julia');
    }

    protected function tearDown(): void
    {
        $this->get('feature_flags')->deleteFile();

        parent::tearDown();
    }

    public function test_it_loads_the_product_grid_with_its_default_columns_filters_and_rows(): void
    {
        $this->get('feature_flags')->disable('data_quality_insights');

        $content = $this->loadProductGrid([]);

        Assert::assertSame(self::DEFAULT_COLUMNS, $this->columnNames($content['metadata']));

        $filterNames = $this->filterNames($content['metadata']);
        foreach (['family', 'enabled', 'completeness', 'created', 'updated', 'scope', 'groups', 'label_or_identifier'] as $systemFilter) {
            Assert::assertContains($systemFilter, $filterNames, 'product.yml system filter');
        }
        // SelectedAttributesConfigurator adds the attributes of the "_filter" param, of the displayed columns and of the
        // user product grid filters. julia has no saved grid filters (technical users.csv), but the default columns
        // display the identifier, so the sku attribute filter is still offered (confirmed on CI).
        Assert::assertSame([], $this->get('pim_user.repository.user')->findOneByIdentifier('julia')->getProductGridFilters());
        Assert::assertContains('sku', $filterNames, 'the displayed identifier column brings its attribute filter');

        Assert::assertSame(
            ['grid_men_summer_product', 'grid_unclassified_product', 'grid_women_product'],
            $this->rowIdentifiers($content)
        );
    }

    public function test_it_adds_the_filters_of_the_attributes_saved_in_the_user_product_grid_filters(): void
    {
        $this->get('feature_flags')->disable('data_quality_insights');
        $julia = $this->get('pim_user.repository.user')->findOneByIdentifier('julia');
        $julia->setProductGridFilters(['sku']);
        $this->get('pim_user.saver.user')->save($julia);
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        // The grid reads the user of the token: log in again so it carries the saved filters.
        $this->logIn('julia');

        $filterNames = $this->filterNames($this->loadProductGrid([])['metadata']);

        Assert::assertContains('sku', $filterNames, 'the sku attribute, useable as grid filter, is added to the filters');
        Assert::assertContains('family', $filterNames, 'the product.yml system filters are kept');
    }

    public function test_the_quality_score_column_and_filters_follow_the_data_quality_insights_feature_flag(): void
    {
        $this->get('feature_flags')->disable('data_quality_insights');
        $metadata = $this->loadProductGrid([])['metadata'];

        Assert::assertNotContains('data_quality_insights_score', $this->columnNames($metadata));
        foreach (self::QUALITY_FILTERS as $qualityFilter) {
            Assert::assertNotContains($qualityFilter, $this->filterNames($metadata));
        }

        $this->get('feature_flags')->enable('data_quality_insights');
        $metadata = $this->loadProductGrid([])['metadata'];

        Assert::assertSame(
            [...self::DEFAULT_COLUMNS, 'data_quality_insights_score'],
            $this->columnNames($metadata)
        );
        foreach (self::QUALITY_FILTERS as $qualityFilter) {
            Assert::assertContains($qualityFilter, $this->filterNames($metadata));
        }
        // The sorter extension flags a column sortable only when its sorter survived the build.before listeners.
        $qualityScoreColumn = $metadata['columns'][\array_search('data_quality_insights_score', $this->columnNames($metadata), true)];
        Assert::assertTrue($qualityScoreColumn['sortable'] ?? false, \json_encode($qualityScoreColumn));
    }

    public function test_it_filters_the_rows_by_the_category_selected_in_the_tree_panel(): void
    {
        $this->get('feature_flags')->disable('data_quality_insights');
        $treeId = $this->categoryId('grid_tree');
        $menId = $this->categoryId('grid_men');

        Assert::assertSame(
            ['grid_men_summer_product'],
            $this->rowIdentifiers($this->loadProductGrid($this->categoryFilter($treeId, $menId, 1))),
            'including sub-categories, grid_men lists the product of grid_men_summer'
        );
        Assert::assertSame(
            [],
            $this->rowIdentifiers($this->loadProductGrid($this->categoryFilter($treeId, $menId, 0))),
            'not including sub-categories, grid_men has no product'
        );
        Assert::assertSame(
            ['grid_women_product'],
            $this->rowIdentifiers($this->loadProductGrid($this->categoryFilter($treeId, $this->categoryId('grid_women'), 0)))
        );
        Assert::assertSame(
            ['grid_unclassified_product'],
            $this->rowIdentifiers($this->loadProductGrid($this->categoryFilter($treeId, -1, 1))),
            'categoryId -1 lists the products not classified in the tree'
        );
        Assert::assertSame(
            ['grid_men_summer_product', 'grid_unclassified_product', 'grid_women_product'],
            $this->rowIdentifiers($this->loadProductGrid($this->categoryFilter($treeId, -2, 1))),
            'categoryId -2 lists all the products'
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param array<string, mixed> $gridState the parameters sent under the "product-grid" root
     *
     * @return array{metadata: array<string, mixed>, data: string}
     */
    private function loadProductGrid(array $gridState): array
    {
        $parameters = ['dataLocale' => 'en_US', 'params' => ['dataLocale' => 'en_US']];
        if ([] !== $gridState) {
            $parameters['product-grid'] = $gridState;
        }

        $this->callApiRoute(
            $this->client,
            'pim_datagrid_load',
            ['alias' => 'product-grid'],
            Request::METHOD_GET,
            $parameters
        );
        $response = $this->client->getResponse();
        $this->assertStatusCode($response, Response::HTTP_OK);

        $content = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertIsArray($content['metadata'] ?? null, (string) $response->getContent());
        Assert::assertIsString($content['data'] ?? null, (string) $response->getContent());

        return $content;
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryFilter(int $treeId, int $categoryId, int $includeSubCategories): array
    {
        return [
            '_filter' => [
                'category' => [
                    'value' => ['treeId' => (string) $treeId, 'categoryId' => (string) $categoryId],
                    'type' => (string) $includeSubCategories,
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $metadata
     *
     * @return list<string>
     */
    private function columnNames(array $metadata): array
    {
        Assert::assertIsArray($metadata['columns'] ?? null, \json_encode($metadata));

        return \array_column($metadata['columns'], 'name');
    }

    /**
     * @param array<string, mixed> $metadata
     *
     * @return list<string>
     */
    private function filterNames(array $metadata): array
    {
        Assert::assertIsArray($metadata['filters'] ?? null, \json_encode($metadata));

        return \array_column($metadata['filters'], 'name');
    }

    /**
     * @param array{data: string} $content
     *
     * @return list<string> the sorted identifiers of the returned rows
     */
    private function rowIdentifiers(array $content): array
    {
        $data = \json_decode($content['data'], true, 512, JSON_THROW_ON_ERROR);
        Assert::assertIsArray($data['data'] ?? null, $content['data']);

        $identifiers = \array_column($data['data'], 'identifier');
        Assert::assertCount(\count($data['data']), $identifiers, 'every row must carry its identifier');
        \sort($identifiers);

        return $identifiers;
    }

    private function categoryId(string $code): int
    {
        $category = $this->get('pim_catalog.repository.category')->findOneByIdentifier($code);
        Assert::assertNotNull($category, sprintf('The category "%s" must exist', $code));

        return $category->getId();
    }
}
