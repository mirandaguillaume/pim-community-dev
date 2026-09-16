<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Controller\InternalApi;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend guard of the product model creation modal: POST pim_enrich_product_model_rest_create
 * (InternalApi\ProductModelController::createAction). Behat create_product_model.feature:107 saved the modal with only
 * a code and expected "The product model family variant must not be empty."; its Playwright spec,
 * tests/front/e2e/product-model/create-product-model.spec.ts, does not run on backend-only changes.
 *
 * The violation is asserted as the internal API sends it (path, message, global), since the modal reads that shape.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateProductModelControllerIntegration extends WebTestCase
{
    protected KernelBrowser $client;

    public function test_a_product_model_without_family_variant_is_rejected_with_the_family_variant_violation(): void
    {
        $this->logIn('admin');

        $response = $this->postToCreateRoute(['code' => 'pm_no_fv']);

        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), (string) $response->getContent());
        Assert::assertSame(
            [
                'values' => [
                    [
                        'path' => 'family_variant',
                        'message' => 'The product model family variant must not be empty.',
                        'global' => false,
                    ],
                ],
            ],
            \json_decode((string) $response->getContent(), true)
        );
        Assert::assertSame(0, $this->countProductModels('pm_no_fv'));
    }

    public function test_a_product_model_with_a_family_variant_is_created(): void
    {
        $this->logIn('admin');

        $response = $this->postToCreateRoute(['code' => 'pm_with_fv', 'family_variant' => 'clothing_color_size']);

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        $content = \json_decode((string) $response->getContent(), true);
        Assert::assertSame('pm_with_fv', $content['code']);

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('pm_with_fv');
        Assert::assertNotNull($productModel, 'The product model "pm_with_fv" was not persisted');
        Assert::assertSame('clothing_color_size', $productModel->getFamilyVariant()?->getCode());
    }

    public function test_a_non_xhr_request_is_redirected_and_creates_nothing(): void
    {
        $this->logIn('admin');

        $response = $this->postToCreateRoute(['code' => 'pm_not_created', 'family_variant' => 'clothing_color_size'], false);

        Assert::assertTrue(
            $response->isRedirect('/'),
            \sprintf('Expected a redirect to "/", got: %d %s', $response->getStatusCode(), $response->getContent())
        );
        Assert::assertSame(0, $this->countProductModels('pm_not_created'));
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

    private function logIn(string $username): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($username, $this->client);
    }

    private function postToCreateRoute(array $payload, bool $xmlHttpRequest = true): Response
    {
        $server = ['CONTENT_TYPE' => 'application/json'];
        if ($xmlHttpRequest) {
            $server['HTTP_X-Requested-With'] = 'XMLHttpRequest';
        }

        $this->client->request(
            Request::METHOD_POST,
            $this->get('router')->generate('pim_enrich_product_model_rest_create'),
            [],
            [],
            $server,
            \json_encode($payload, JSON_THROW_ON_ERROR)
        );

        return $this->client->getResponse();
    }

    /**
     * Read the database directly, so Doctrine's identity map cannot answer.
     */
    private function countProductModels(string $code): int
    {
        return (int) $this->get('database_connection')->executeQuery(
            'SELECT COUNT(*) FROM pim_catalog_product_model WHERE code = :code',
            ['code' => $code]
        )->fetchOne();
    }
}
