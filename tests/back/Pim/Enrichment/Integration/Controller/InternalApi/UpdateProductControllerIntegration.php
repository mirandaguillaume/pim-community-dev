<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Doctrine\Common\Util\ClassUtils;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend guard of the product edit form save: the form GETs the product from pim_enrich_product_rest_get, then POSTs
 * it back without its meta to pim_enrich_product_rest_post (UpdateProductController). It replaces the PR-time
 * protection of the Behat scenarios edit_product.feature:31 (edit and save), edit_product.feature:43 (the updated date
 * moves) and create_product_and_save_added_attributes.feature:9 (PIM-5666, zero values survive the save). Their
 * Playwright specs, tests/front/e2e/critical/product-crud.spec.ts and
 * tests/front/e2e/product/create-product-added-attributes.spec.ts, do not run on backend-only changes.
 *
 * The categories part of the same POST is guarded by UpdateProductCategoriesControllerIntegration.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateProductControllerIntegration extends WebTestCase
{
    use AssertEventCountTrait;

    protected KernelBrowser $client;

    public function test_the_get_route_returns_the_product_values_and_meta(): void
    {
        $uuid = $this->createProduct('edit_me', [new SetTextValue('a_text', null, null, 'initial')]);
        $this->logIn('admin');

        $product = $this->getProductFromInternalApi($uuid);

        Assert::assertSame($uuid, $product['meta']['uuid']);
        Assert::assertSame('product', $product['meta']['model_type']);
        Assert::assertSame('initial', $product['values']['a_text'][0]['data']);
    }

    public function test_saving_an_edited_value_stores_it_versions_it_and_moves_the_updated_date(): void
    {
        $uuid = $this->createProduct('edit_me', [new SetTextValue('a_text', null, null, 'initial')]);
        // Same as the Behat step "I set the updated date of the product": push the date far in the past first.
        $this->setStoredUpdatedDate($uuid, '2000-01-01 00:00:00');
        Assert::assertStringStartsWith('2000-01-01', $this->getStoredUpdatedDate($uuid));
        Assert::assertCount(1, $this->getVersions($uuid));

        $this->logIn('admin');
        $product = $this->getProductFromInternalApi($uuid);
        $product['values']['a_text'][0]['data'] = 'edited';
        unset($product['meta']);
        $this->clearMessageBusObserver();

        $response = $this->postProductToInternalApi($uuid, $product);

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        $content = \json_decode((string) $response->getContent(), true);
        Assert::assertSame('edited', $content['values']['a_text'][0]['data']);
        $this->assertEventCount(1, ProductUpdated::class);
        Assert::assertSame('edited', $this->findStoredProduct($uuid)->getValue('a_text')?->getData());

        $updated = new \DateTimeImmutable($this->getStoredUpdatedDate($uuid), new \DateTimeZone('UTC'));
        // One day of tolerance keeps the assertion independent of the timezone the column is written in.
        Assert::assertLessThan(
            86400,
            \abs(\time() - $updated->getTimestamp()),
            \sprintf('The updated date was not moved to now by the save, it is still "%s"', $updated->format('c'))
        );

        $versions = $this->getVersions($uuid);
        Assert::assertCount(2, $versions);
        $newestVersion = $versions[0];
        Assert::assertSame(2, $newestVersion->getVersion());
        Assert::assertSame('edited', $newestVersion->getChangeset()['a_text']['new'] ?? null);

        // Saving the form again without any change must not create a new version.
        $unchangedProduct = $this->getProductFromInternalApi($uuid);
        unset($unchangedProduct['meta']);
        $response = $this->postProductToInternalApi($uuid, $unchangedProduct);

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        Assert::assertCount(2, $this->getVersions($uuid));
    }

    /**
     * PIM-5666: a 0 typed in a number, a metric or a price field used to be dropped by the save.
     */
    public function test_zero_number_metric_and_price_values_added_to_a_product_are_saved(): void
    {
        $uuid = $this->createProduct('zero_values');
        $this->logIn('admin');
        $product = $this->getProductFromInternalApi($uuid);

        // Same shapes as the edit form sends when these attributes are added to the product.
        $product['values']['a_number_float'] = [['locale' => null, 'scope' => null, 'data' => '0']];
        $product['values']['a_metric'] = [
            ['locale' => null, 'scope' => null, 'data' => ['amount' => '0', 'unit' => 'KILOWATT']],
        ];
        $product['values']['a_price'] = [
            [
                'locale' => null,
                'scope' => null,
                'data' => [['amount' => '0', 'currency' => 'EUR'], ['amount' => null, 'currency' => 'USD']],
            ],
        ];
        unset($product['meta']);

        $response = $this->postProductToInternalApi($uuid, $product);

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        $content = \json_decode((string) $response->getContent(), true);
        Assert::assertArrayHasKey('a_number_float', $content['values']);
        Assert::assertArrayHasKey('a_metric', $content['values']);
        Assert::assertArrayHasKey('a_price', $content['values']);

        $storedProduct = $this->findStoredProduct($uuid);

        $number = $storedProduct->getValue('a_number_float');
        Assert::assertNotNull($number, 'The 0 number value was not saved');
        Assert::assertSame(0.0, (float) $number->getData());

        $metric = $storedProduct->getValue('a_metric');
        Assert::assertNotNull($metric, 'The 0 metric value was not saved');
        Assert::assertSame(0.0, (float) $metric->getData()->getData());
        Assert::assertSame('KILOWATT', $metric->getData()->getUnit());

        $price = $storedProduct->getValue('a_price');
        Assert::assertNotNull($price, 'The 0 price value was not saved');
        Assert::assertNotNull($price->getPrice('EUR'), 'The 0 EUR price was not saved');
        Assert::assertSame(0.0, (float) $price->getPrice('EUR')->getData());
        Assert::assertNull($price->getPrice('USD'), 'The empty USD price must not be saved');
    }

    public function test_an_invalid_number_is_rejected_and_the_product_is_left_unchanged(): void
    {
        $uuid = $this->createProduct('invalid_number', [new SetNumberValue('a_number_float', null, null, '12.5')]);
        $this->logIn('admin');
        $product = $this->getProductFromInternalApi($uuid);
        $product['values']['a_number_float'][0]['data'] = 'not a number';
        unset($product['meta']);

        $response = $this->postProductToInternalApi($uuid, $product);

        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), (string) $response->getContent());
        $content = \json_decode((string) $response->getContent(), true);
        Assert::assertNotEmpty($content['values'] ?? [], 'The 400 response has no "values" violation');
        Assert::assertSame(12.5, (float) $this->findStoredProduct($uuid)->getValue('a_number_float')?->getData());
    }

    public function test_a_non_xhr_post_is_redirected_and_saves_nothing(): void
    {
        $uuid = $this->createProduct('edit_me', [new SetTextValue('a_text', null, null, 'initial')]);
        $this->logIn('admin');
        $product = $this->getProductFromInternalApi($uuid);
        $product['values']['a_text'][0]['data'] = 'edited';
        unset($product['meta']);

        $response = $this->postProductToInternalApi($uuid, $product, false);

        Assert::assertTrue(
            $response->isRedirect('/'),
            \sprintf('Expected a redirect to "/", got: %d %s', $response->getStatusCode(), $response->getContent())
        );
        Assert::assertSame('initial', $this->findStoredProduct($uuid)->getValue('a_text')?->getData());
    }

    public function test_an_unknown_product_uuid_returns_a_404(): void
    {
        $unknownUuid = Uuid::uuid4()->toString();
        $this->logIn('admin');

        $this->client->request(
            Request::METHOD_GET,
            $this->get('router')->generate('pim_enrich_product_rest_get', ['uuid' => $unknownUuid]),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
        Assert::assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());

        $response = $this->postProductToInternalApi($unknownUuid, ['values' => []]);
        Assert::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
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
            $this->get('router')->generate('pim_enrich_product_rest_get', ['uuid' => $uuid]),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());

        return \json_decode((string) $response->getContent(), true);
    }

    private function postProductToInternalApi(string $uuid, array $product, bool $xmlHttpRequest = true): Response
    {
        // With disableReboot() the same container serves every request. UniqueValuesSet still holds the identifier
        // registered by a previous validation, which would reject this save as "identifier already used" (same reset
        // as VersioningControllerIntegration and UpdateProductCategoriesControllerIntegration).
        $this->get('pim_catalog.validator.unique_value_set')->reset();

        $server = ['CONTENT_TYPE' => 'application/json'];
        if ($xmlHttpRequest) {
            $server['HTTP_X-Requested-With'] = 'XMLHttpRequest';
        }

        $this->client->request(
            Request::METHOD_POST,
            $this->get('router')->generate('pim_enrich_product_rest_post', ['uuid' => $uuid]),
            [],
            [],
            $server,
            \json_encode($product, JSON_THROW_ON_ERROR)
        );

        return $this->client->getResponse();
    }

    private function findStoredProduct(string $uuid): ProductInterface
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $product = $this->get('pim_catalog.repository.product')->find($uuid);
        Assert::assertNotNull($product, \sprintf('Product %s not found', $uuid));

        return $product;
    }

    /**
     * @return Version[] newest version first
     */
    private function getVersions(string $uuid): array
    {
        $product = $this->findStoredProduct($uuid);
        $versions = $this->get('pim_versioning.repository.version')->getLogEntries(
            ClassUtils::getClass($product),
            null,
            $product->getUuid()
        );
        \usort($versions, fn(Version $a, Version $b): int => $b->getVersion() <=> $a->getVersion());

        return $versions;
    }

    private function setStoredUpdatedDate(string $uuid, string $date): void
    {
        $this->get('database_connection')->executeStatement(
            'UPDATE pim_catalog_product SET updated = :updated WHERE uuid = :uuid',
            ['updated' => $date, 'uuid' => Uuid::fromString($uuid)->getBytes()]
        );
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    private function getStoredUpdatedDate(string $uuid): string
    {
        return (string) $this->get('database_connection')->executeQuery(
            'SELECT updated FROM pim_catalog_product WHERE uuid = :uuid',
            ['uuid' => Uuid::fromString($uuid)->getBytes()]
        )->fetchOne();
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
}
