<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\tests\Integration\Controller\Rest;

use Akeneo\Test\Integration\Configuration;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\tests\Integration\Controller\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend guard of the two requests the product grid sends before loading (view-selector and product/grid/table.js),
 * whose UI flow is covered by tests/front/e2e/product/datagrid-views.spec.ts (formerly Behat
 * datagrid_views.feature:17):
 * - pim_datagrid_view_rest_default_user_view answers {view: null} or the user's normalized default view of the grid;
 * - pim_datagrid_view_rest_default_columns answers the ordered column codes of the raw grid configuration.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DefaultDatagridViewIntegration extends ControllerIntegrationTestCase
{
    public function test_it_returns_no_view_when_the_user_has_no_default_view_for_the_grid(): void
    {
        $this->logIn('mary');

        Assert::assertSame(['view' => null], $this->getJson('pim_datagrid_view_rest_default_user_view', 'product-grid'));
    }

    public function test_it_returns_the_normalized_default_view_of_the_requested_grid(): void
    {
        $filters = 'i=1&p=25&s[updated]=1&f[scope][value]=ecommerce&f[category][value][treeId]=1&f[category][value][categoryId]=-2&f[category][type]=1&t=product-grid';
        $productGridView = $this->createView('product-grid', 'Mary product view', ['identifier', 'family', 'completeness'], $filters);
        $attributeGridView = $this->createView('attribute-grid', 'Mary attribute view', ['code'], 'i=1&p=25&t=attribute-grid');

        $mary = $this->get('pim_user.repository.user')->findOneByIdentifier('mary');
        $maryId = $mary->getId();
        $mary->setDefaultGridView('attribute-grid', $attributeGridView);
        $mary->setDefaultGridView('product-grid', $productGridView);
        $this->get('pim_user.saver.user')->save($mary);
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        $this->logIn('mary');

        Assert::assertSame(
            [
                'view' => [
                    'id' => $productGridView->getId(),
                    'owner_id' => $maryId,
                    'label' => 'Mary product view',
                    'type' => DatagridView::TYPE_PUBLIC,
                    'datagrid_alias' => 'product-grid',
                    'columns' => ['identifier', 'family', 'completeness'],
                    'filters' => $filters,
                ],
            ],
            $this->getJson('pim_datagrid_view_rest_default_user_view', 'product-grid')
        );
        Assert::assertSame(
            'Mary attribute view',
            $this->getJson('pim_datagrid_view_rest_default_user_view', 'attribute-grid')['view']['label'] ?? null,
            'the default view is picked by grid alias'
        );
    }

    public function test_it_returns_the_default_columns_of_the_product_grid_in_order(): void
    {
        $this->logIn('mary');

        // The raw configuration is read without the build.before listeners, so the feature-flagged
        // data_quality_insights_score column (DataQualityInsights datagrid yml, merged after the Enrichment one) is
        // always listed here.
        Assert::assertSame(
            [
                'identifier',
                'image',
                'label',
                'family',
                'enabled',
                'completeness',
                'created',
                'updated',
                'complete_variant_products',
                'data_quality_insights_score',
            ],
            $this->getJson('pim_datagrid_view_rest_default_columns', 'product-grid')
        );
    }

    public function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getJson(string $route, string $alias): array
    {
        $this->callApiRoute($this->client, $route, ['alias' => $alias], Request::METHOD_GET);
        $response = $this->client->getResponse();
        $this->assertStatusCode($response, Response::HTTP_OK);

        $content = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertIsArray($content, (string) $response->getContent());

        return $content;
    }

    /**
     * @param list<string> $columns
     */
    private function createView(string $alias, string $label, array $columns, string $filters): DatagridView
    {
        $datagridView = new DatagridView();
        $datagridView->setDatagridAlias($alias);
        $datagridView->setLabel($label);
        $datagridView->setType(DatagridView::TYPE_PUBLIC);
        $datagridView->setOwner($this->get('pim_user.repository.user')->findOneByIdentifier('mary'));
        $datagridView->setColumns($columns);
        $datagridView->setFilters($filters);
        $this->get('pim_datagrid.saver.datagrid_view')->save($datagridView);

        return $datagridView;
    }
}
