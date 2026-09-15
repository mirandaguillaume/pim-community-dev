<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Controller;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Doctrine\DBAL\ArrayParameterType;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the backend entry point of the product model "Delete" secondary action:
 * DELETE /enrich/product-model/rest/{id} (pim_enrich_product_model_rest_remove,
 * InternalApi\ProductModelController::removeAction). This is the request the legacy Behat scenario
 * product-model/remove.feature:12 sent as Julia. The Playwright spec that replaced it
 * (tests/front/e2e/product-model/remove-product-model.spec.ts) is path-filtered out of backend-only
 * changes, so this test keeps the controller path covered on those.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveProductModelControllerIntegration extends WebTestCase
{
    use AssertEventCountTrait;

    private const ROOT_CODE = 'root_product_model_two_level';
    private const SUB_CODE = 'sub_product_model';
    private const VARIANT_IDENTIFIER = 'variant_product_1';

    protected KernelBrowser $client;

    public function test_an_xhr_delete_removes_the_product_model_and_its_descendants_everywhere(): void
    {
        [$root, , $variant] = $this->arrange();

        $esClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
        $esClient->refreshIndex();
        // Positive controls, so the absence checks below cannot pass on a tree that was never persisted.
        self::assertSame(2, $this->countProductModelRows());
        self::assertSame(1, $this->countProductRows($variant));
        self::assertTrue($this->isInIndex(self::ROOT_CODE));
        self::assertTrue($this->isInIndex(self::SUB_CODE));
        self::assertTrue($this->isInIndex(self::VARIANT_IDENTIFIER));

        $this->clearMessageBusObserver();
        $this->logIn('julia');
        $this->client->request(
            Request::METHOD_DELETE,
            $this->get('router')->generate('pim_enrich_product_model_rest_remove', ['id' => $root->getId()]),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $response = $this->client->getResponse();
        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
            \sprintf('Unexpected response: %d %s', $response->getStatusCode(), $response->getContent())
        );

        // The sub product model and the variant product go through the database cascade.
        self::assertSame(0, $this->countProductModelRows());
        self::assertSame(0, $this->countProductRows($variant));

        // No refreshIndex() here on purpose: removeAction refreshes the index before answering, which is what
        // lets the product grid the UI redirects to (pim_enrich_product_index) stop listing the whole tree.
        self::assertFalse($this->isInIndex(self::ROOT_CODE));
        self::assertFalse($this->isInIndex(self::SUB_CODE));
        self::assertFalse($this->isInIndex(self::VARIANT_IDENTIFIER));

        // Behat: 1 event of type "product_model.removed", 0 event of type "product.removed".
        $this->assertEventCount(1, ProductModelRemoved::class);
        $this->assertEventCount(0, ProductRemoved::class);
    }

    public function test_a_non_xhr_delete_is_redirected_and_removes_nothing(): void
    {
        [$root, , $variant] = $this->arrange();

        $this->logIn('julia');
        $this->client->request(
            Request::METHOD_DELETE,
            $this->get('router')->generate('pim_enrich_product_model_rest_remove', ['id' => $root->getId()])
        );

        $response = $this->client->getResponse();
        self::assertTrue(
            $response->isRedirect('/'),
            \sprintf('Expected a redirect to "/", got: %d %s', $response->getStatusCode(), $response->getContent())
        );
        self::assertSame(2, $this->countProductModelRows());
        self::assertSame(1, $this->countProductRows($variant));
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

    private function logIn(string $username): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($username, $this->client);
    }

    /**
     * Same tree as RemoveProductModelIntegration::arrange(): 1 root, 1 sub product model, 1 variant product.
     *
     * @return array{ProductModelInterface, ProductModelInterface, ProductInterface}
     */
    private function arrange(): array
    {
        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');
        $entityBuilder->createFamilyVariant(
            [
                'code' => 'two_level_family_variant',
                'family' => 'familyA3',
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'axes' => ['a_simple_select'],
                        'attributes' => ['a_text'],
                    ],
                    [
                        'level' => 2,
                        'axes' => ['a_yes_no'],
                        'attributes' => ['sku', 'a_localized_and_scopable_text_area'],
                    ],
                ],
            ]
        );

        $root = $entityBuilder->createProductModel(self::ROOT_CODE, 'two_level_family_variant', null, []);
        $sub = $entityBuilder->createProductModel(self::SUB_CODE, 'two_level_family_variant', $root, []);
        $variant = $entityBuilder->createVariantProduct(
            self::VARIANT_IDENTIFIER,
            'familyA3',
            'two_level_family_variant',
            $sub,
            []
        );

        return [$root, $sub, $variant];
    }

    /**
     * Read the database directly, not through the repositories, so Doctrine's identity map cannot answer.
     */
    private function countProductModelRows(): int
    {
        return (int) $this->get('database_connection')->executeQuery(
            'SELECT COUNT(*) FROM pim_catalog_product_model WHERE code IN (:codes)',
            ['codes' => [self::ROOT_CODE, self::SUB_CODE]],
            ['codes' => ArrayParameterType::STRING]
        )->fetchOne();
    }

    private function countProductRows(ProductInterface $product): int
    {
        return (int) $this->get('database_connection')->executeQuery(
            'SELECT COUNT(*) FROM pim_catalog_product WHERE uuid = :uuid',
            ['uuid' => $product->getUuid()->getBytes()]
        )->fetchOne();
    }

    private function isInIndex(string $identifier): bool
    {
        $result = $this->get('akeneo_elasticsearch.client.product_and_product_model')->search(
            ['query' => ['term' => ['identifier' => $identifier]]]
        );

        return $result['hits']['total']['value'] > 0;
    }
}
