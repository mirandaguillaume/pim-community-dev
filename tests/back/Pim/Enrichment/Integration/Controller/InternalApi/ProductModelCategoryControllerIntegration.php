<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend guard of the product model edit form's Categories tab: GET pim_enrich_product_model_category_rest_list
 * (InternalApi\ProductModelCategoryController::listAction), which is the only caller of
 * ProductModelCategoryRepository::getItemCountByTree().
 *
 * Behat classify_product_model.feature:26 "Count sub product model categories" was the last test executing that
 * repository. Its Playwright replacement, tests/front/e2e/product-model/classify-product-model.spec.ts, exercises the
 * same UI but test-playwright does not run on backend-only pull requests, so the path had no backend guard left.
 *
 * The two halves of the response deliberately disagree, and that disagreement IS the contract:
 *  - "categories" comes from ProductModel::getCategories(), which walks up to the parent, so a sub product model
 *    reports its own categories AND its root's;
 *  - "trees" comes from getItemCountByTree(), a direct SQL count of the rows linking THIS product model to a
 *    category, so an inherited category does not mark its tree as associated.
 * A change that made either side agree with the other would pass a laxer test and break the form.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelCategoryControllerIntegration extends WebTestCase
{
    protected KernelBrowser $client;

    public function test_a_sub_product_model_reports_its_own_categories_and_its_parents(): void
    {
        $subProductModel = $this->createHierarchy();
        $this->logIn('admin');

        $content = $this->getCategories($subProductModel->getId());

        // tshirts and master_men_shoes are its own; print_clothing is inherited from the root product model.
        Assert::assertEqualsCanonicalizing(
            ['tshirts', 'master_men_shoes', 'print_clothing'],
            \array_column($content['categories'], 'code')
        );
    }

    public function test_only_a_tree_holding_one_of_its_own_categories_is_marked_associated(): void
    {
        $subProductModel = $this->createHierarchy();
        $this->logIn('admin');

        $content = $this->getCategories($subProductModel->getId());

        $associated = [];
        foreach ($content['trees'] as $tree) {
            $associated[$tree['code']] = $tree['associated'];
        }
        Assert::assertArrayHasKey('master', $associated, 'the master tree is missing from the response');
        Assert::assertTrue($associated['master'], 'master holds tshirts and master_men_shoes, both its own');
        // print holds print_clothing, but only through the ROOT product model: getItemCountByTree counts the rows of
        // this product model alone, so the tree must not be flagged even though "categories" above lists it.
        Assert::assertArrayHasKey('print', $associated, 'the print tree is missing from the response');
        Assert::assertFalse($associated['print'], 'print holds only an inherited category');
        Assert::assertArrayHasKey('suppliers', $associated, 'the suppliers tree is missing from the response');
        Assert::assertFalse($associated['suppliers'], 'suppliers holds no category of this product model');
    }

    public function test_an_unknown_product_model_is_a_404(): void
    {
        $this->logIn('admin');

        $this->client->request(
            Request::METHOD_GET,
            $this->get('router')->generate('pim_enrich_product_model_category_rest_list', ['id' => '99999999']),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        Assert::assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($this->get('akeneo_integration_tests.catalogs')->useFunctionalCatalog('catalog_modeling'));

        $this->get('akeneo_integration_tests.security.system_user_authenticator')->createSystemUser();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function get(string $service)
    {
        return self::getContainer()->get($service);
    }

    /**
     * A root product model classified in the "print" tree, and a sub product model classified twice in "master".
     */
    private function createHierarchy(): ProductModelInterface
    {
        $this->createProductModel([
            'code' => 'pm_guard_root',
            'family_variant' => 'clothing_color_size',
            'categories' => ['print_clothing'],
        ]);

        return $this->createProductModel([
            'code' => 'pm_guard_blue',
            'parent' => 'pm_guard_root',
            'family_variant' => 'clothing_color_size',
            'categories' => ['tshirts', 'master_men_shoes'],
            'values' => [
                'color' => [['locale' => null, 'scope' => null, 'data' => 'blue']],
            ],
        ]);
    }

    private function createProductModel(array $data): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($productModel);
        Assert::assertCount(
            0,
            $errors,
            \sprintf('Cannot set up "%s": %s', $data['code'], 0 === $errors->count() ? '' : $errors->get(0)->getMessage())
        );

        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        return $productModel;
    }

    private function logIn(string $username): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($username, $this->client);
    }

    private function getCategories(int $productModelId): array
    {
        $this->client->request(
            Request::METHOD_GET,
            $this->get('router')->generate('pim_enrich_product_model_category_rest_list', ['id' => (string) $productModelId]),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());

        return \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }
}
