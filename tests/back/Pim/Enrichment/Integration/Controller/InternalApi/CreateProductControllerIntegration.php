<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend guard of POST pim_enrich_product_rest_create (InternalApi\ProductController::createAction). Two UI flows send
 * it: the product creation modal ({identifier, family}) and the "Add new" modal of a sub product model, which also sends
 * the parent and the axis values (formerly Behat add_product_model_children.feature:93). Their Playwright specs,
 * tests/front/e2e/product/create-product-added-attributes.spec.ts and
 * tests/front/e2e/product-model/add-product-model-children.spec.ts, do not run on backend-only changes.
 *
 * This class also replaces ProductCreationEndToEnd, which belonged to the End_to_End suite that no CI job runs.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateProductControllerIntegration extends WebTestCase
{
    use AssertEventCountTrait;

    protected KernelBrowser $client;

    public function test_it_creates_a_product_in_a_family_and_raises_one_product_created_event(): void
    {
        $this->logIn('admin');
        $this->clearMessageBusObserver();

        $response = $this->postToCreateRoute(['identifier' => 'new_clothing', 'family' => 'clothing']);

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        $content = \json_decode((string) $response->getContent(), true);
        Assert::assertTrue(Uuid::isValid($content['meta']['uuid'] ?? ''), 'The response has no valid meta.uuid');
        Assert::assertSame('clothing', $content['family']);
        $this->assertEventCount(1, ProductCreated::class);

        $product = $this->findStoredProduct('new_clothing');
        Assert::assertNotNull($product, 'The product "new_clothing" was not persisted');
        Assert::assertSame($content['meta']['uuid'], $product->getUuid()->toString());
        Assert::assertSame('clothing', $product->getFamily()?->getCode());

        // Ported from ProductCreationEndToEnd::testThatWeCanFetchANewlyCreatedProductFromTheUI.
        $connectorProducts = $this->get('akeneo.pim.enrichment.product.connector.get_product_from_uuids')
            ->fromProductUuids([$product->getUuid()], $this->getUserId('admin'), null, null, null)
            ->connectorProducts();
        Assert::assertCount(1, $connectorProducts);
        Assert::assertSame('new_clothing', $connectorProducts[0]->identifier());
    }

    /**
     * Ported from ProductCreationEndToEnd::testThatWeCanCreateAProductWithoutIdentifier.
     */
    public function test_it_creates_a_product_without_identifier(): void
    {
        $previousCount = $this->countProducts();
        $this->logIn('admin');

        $response = $this->postToCreateRoute(['identifier' => '']);

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        Assert::assertSame($previousCount + 1, $this->countProducts());
    }

    public function test_it_creates_a_variant_product_under_a_sub_product_model(): void
    {
        // catalog_modeling: apollon_blue is the level-1 product model of clothing_color_size (axis color) and its
        // variant products use the sizes xxl, m and s, so xl is free.
        $this->logIn('admin');
        $this->clearMessageBusObserver();

        $response = $this->postToCreateRoute($this->variantProductPayload('apollon_blue_xl', 'xl'));

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        $content = \json_decode((string) $response->getContent(), true);
        Assert::assertTrue(Uuid::isValid($content['meta']['uuid'] ?? ''), 'The response has no valid meta.uuid');
        $this->assertEventCount(1, ProductCreated::class);

        $product = $this->findStoredProduct('apollon_blue_xl');
        Assert::assertNotNull($product, 'The variant product "apollon_blue_xl" was not persisted');
        Assert::assertTrue($product->isVariant());
        Assert::assertSame('apollon_blue', $product->getParent()?->getCode());
        Assert::assertSame('clothing', $product->getFamily()?->getCode());
        Assert::assertSame('xl', $product->getValue('size')?->getData());
    }

    public function test_a_variant_product_without_its_axis_value_is_rejected(): void
    {
        $this->logIn('admin');

        $response = $this->postToCreateRoute($this->variantProductPayload('apollon_blue_no_size', null));

        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), (string) $response->getContent());
        Assert::assertContains(
            'Attribute "size" cannot be empty, as it is defined as an axis for this entity',
            $this->getViolationMessages($response)
        );
        Assert::assertNull($this->findStoredProduct('apollon_blue_no_size'));
    }

    public function test_a_variant_product_reusing_an_existing_axis_combination_is_rejected(): void
    {
        $this->logIn('admin');

        $response = $this->postToCreateRoute($this->variantProductPayload('apollon_blue_m_bis', 'm'));

        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), (string) $response->getContent());
        // Same message as CreateVariantProductIntegration::testVariantAxisValuesCombinationIsUniqueInDatabase.
        Assert::assertContains(
            'Cannot set value "[m]" for the attribute axis "size" on variant product "apollon_blue_m_bis", as the variant product "1111111120" already has this value',
            $this->getViolationMessages($response)
        );
        Assert::assertNull($this->findStoredProduct('apollon_blue_m_bis'));
    }

    public function test_a_non_xhr_request_is_redirected_and_creates_nothing(): void
    {
        $this->logIn('admin');

        $response = $this->postToCreateRoute(['identifier' => 'not_created', 'family' => 'clothing'], false);

        Assert::assertTrue(
            $response->isRedirect('/'),
            \sprintf('Expected a redirect to "/", got: %d %s', $response->getStatusCode(), $response->getContent())
        );
        Assert::assertNull($this->findStoredProduct('not_created'));
    }

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        // Keep one container for the whole test: the message bus observer must see what the request dispatches.
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

    private function logIn(string $username): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($username, $this->client);
    }

    /**
     * The payload of the "Add new" modal of a sub product model for a variant product.
     */
    private function variantProductPayload(string $identifier, ?string $size): array
    {
        return [
            'identifier' => $identifier,
            'family' => 'clothing',
            'parent' => 'apollon_blue',
            'values' => [
                'size' => [['locale' => null, 'scope' => null, 'data' => $size]],
            ],
        ];
    }

    private function postToCreateRoute(array $payload, bool $xmlHttpRequest = true): Response
    {
        $server = ['CONTENT_TYPE' => 'application/json'];
        if ($xmlHttpRequest) {
            $server['HTTP_X-Requested-With'] = 'XMLHttpRequest';
        }

        $this->client->request(
            Request::METHOD_POST,
            $this->get('router')->generate('pim_enrich_product_rest_create'),
            [],
            [],
            $server,
            \json_encode($payload, JSON_THROW_ON_ERROR)
        );

        return $this->client->getResponse();
    }

    /**
     * @return string[]
     */
    private function getViolationMessages(Response $response): array
    {
        $content = \json_decode((string) $response->getContent(), true);
        Assert::assertIsArray($content['values'] ?? null, 'The 400 response has no "values" violation list');

        return \array_column($content['values'], 'message');
    }

    private function findStoredProduct(string $identifier): ?ProductInterface
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    private function countProducts(): int
    {
        return (int) $this->get('database_connection')->executeQuery('SELECT COUNT(*) FROM pim_catalog_product')->fetchOne();
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
