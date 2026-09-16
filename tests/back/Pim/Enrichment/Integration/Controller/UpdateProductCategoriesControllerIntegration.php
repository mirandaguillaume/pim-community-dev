<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Controller;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * Backend guard of the product edit form's Categories tab save, whose UI flow is covered by
 * tests/front/e2e/product/classify-product.spec.ts (formerly Behat classify_product.feature). The form POSTs the whole
 * product to pim_enrich_product_rest_post (UpdateProductController) with the categories ticked in every tree. One
 * such request must store exactly those categories and raise exactly one product.updated event.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateProductCategoriesControllerIntegration extends WebTestCase
{
    use AssertEventCountTrait;

    protected KernelBrowser $client;

    public function test_saving_categories_of_two_trees_stores_them_and_raises_one_product_updated_event(): void
    {
        $uuid = $this->createProduct('classify_me');
        $this->logIn('admin');
        $product = $this->getProductFromInternalApi($uuid);
        Assert::assertSame([], $product['categories']);

        // categoryA is under the master tree, master_china is a tree of its own (technical catalog categories.csv).
        $product['categories'] = ['categoryA', 'master_china'];
        unset($product['meta']);
        $this->clearMessageBusObserver();

        $response = $this->postProductToInternalApi($uuid, $product);

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        $this->assertEventCount(1, ProductUpdated::class);
        $content = \json_decode((string) $response->getContent(), true);
        Assert::assertEqualsCanonicalizing(['categoryA', 'master_china'], $content['categories']);
        Assert::assertSame(['categoryA', 'master_china'], $this->getStoredCategoryCodes($uuid));
    }

    public function test_unticking_a_category_stores_the_remaining_ones_and_raises_one_product_updated_event(): void
    {
        $uuid = $this->createProduct('classify_me', [new SetCategories(['categoryA', 'master_china'])]);
        $this->logIn('admin');
        $product = $this->getProductFromInternalApi($uuid);
        Assert::assertEqualsCanonicalizing(['categoryA', 'master_china'], $product['categories']);

        $product['categories'] = ['master_china'];
        unset($product['meta']);
        $this->clearMessageBusObserver();

        $response = $this->postProductToInternalApi($uuid, $product);

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        $this->assertEventCount(1, ProductUpdated::class);
        Assert::assertSame(['master_china'], $this->getStoredCategoryCodes($uuid));
    }

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        // Keep one container for the whole test: the message bus observer must see what the requests dispatch.
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

    /**
     * @param UserIntent[] $userIntents
     */
    private function createProduct(string $identifier, array $userIntents = []): string
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createWithIdentifier(
            userId: $this->getUserId('admin'),
            productIdentifier: ProductIdentifier::fromIdentifier($identifier),
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        // With disableReboot() the same container serves the requests below. UniqueValuesSet keyed the new product's
        // identifier by spl_object_hash when it was created, while the POST validates the stored product by uuid, so
        // without a reset the save is rejected as "identifier already used" (same reset as VersioningControllerIntegration).
        $this->get('pim_catalog.validator.unique_value_set')->reset();

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
        Assert::assertNotNull($product, \sprintf('Product "%s" was not created', $identifier));

        return $product->getUuid()->toString();
    }

    private function logIn(string $username): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($username, $this->client);
    }

    private function getProductFromInternalApi(string $uuid): array
    {
        $this->client->request(
            Request::METHOD_GET,
            $this->getRouter()->generate('pim_enrich_product_rest_get', ['uuid' => $uuid]),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());

        return \json_decode((string) $response->getContent(), true);
    }

    private function postProductToInternalApi(string $uuid, array $product): Response
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->getRouter()->generate('pim_enrich_product_rest_post', ['uuid' => $uuid]),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'],
            \json_encode($product, JSON_THROW_ON_ERROR)
        );

        return $this->client->getResponse();
    }

    /**
     * @return string[] sorted category codes, read back from the database
     */
    private function getStoredCategoryCodes(string $uuid): array
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $product = $this->get('pim_catalog.repository.product')->find($uuid);
        Assert::assertNotNull($product, \sprintf('Product %s not found', $uuid));
        $codes = $product->getCategoryCodes();
        sort($codes);

        return $codes;
    }

    private function getUserId(string $username): int
    {
        $id = $this->get('database_connection')->executeQuery(
            'SELECT id FROM oro_user WHERE username = :username',
            ['username' => $username]
        )->fetchOne();
        if (false === $id || null === $id) {
            throw new \InvalidArgumentException(\sprintf('No user exists with username "%s"', $username));
        }

        return \intval($id);
    }

    private function getRouter(): RouterInterface
    {
        return $this->get('router');
    }
}
