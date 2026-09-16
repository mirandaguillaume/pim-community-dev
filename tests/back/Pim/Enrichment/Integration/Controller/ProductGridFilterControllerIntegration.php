<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Controller;

use Akeneo\Test\IntegrationTestsBundle\Helper\WebClientHelper;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP-level guard for GET /enrich/product-grid-filter/ (route pim_enrich_product_grid_filters,
 * ProductGridFilterController::listAction). The endpoint feeds the "Product grid filters" user
 * preference: the select2 search list and the saved chips.
 *
 * It keeps a backend-gated check on the endpoint the removed Behat scenario
 * edit_user.feature "Successfully edit and apply user preferences" exercised end to end. The
 * Playwright spec tests/front/e2e/user-management/edit-user.spec.ts still covers the UI flow, but
 * test-playwright does not run on backend-only PRs. ProductGridFilterControllerTest mocks every
 * collaborator, so only this test hits the real grid configuration, translator, attribute repository
 * and normalizer.
 *
 * Requests mirror the two real callers, with every option sent as a query-string string:
 * - simple-select-async.js select2Data: search + options {limit, page, catalogLocale};
 * - product-grid-filters.ts initSelection: identifiers + options {limit: 100}.
 *
 * Technical catalog: sku is the only attribute with useable_as_grid_filter=1, a_date has 0.
 * System filters (datagrid/product.yml plus the DQI ones) are asserted by membership only, never as
 * exact lists or counts, because other bundles extend them.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductGridFilterControllerIntegration extends WebTestCase
{
    private KernelBrowser $client;
    private WebClientHelper $webClientHelper;

    public function test_it_lists_translated_system_filters_and_only_grid_filterable_attributes(): void
    {
        // The controller subtracts the matched system filters from the limit before it queries the
        // attributes. 100 leaves room for every technical attribute whatever the number of system filters.
        $filters = $this->fetchFilters([
            'search' => '',
            'options' => ['limit' => '100', 'page' => '1', 'catalogLocale' => 'en_US'],
        ]);

        $systemFilters = $this->systemFilters($filters);
        $systemCodes = \array_column($systemFilters, 'code');
        Assert::assertContains('family', $systemCodes);
        Assert::assertContains('enabled', $systemCodes);
        Assert::assertContains('groups', $systemCodes);
        Assert::assertNotContains('scope', $systemCodes);
        $family = $systemFilters[\array_search('family', $systemCodes, true)];
        Assert::assertSame('Family', $family['labels']['en_US'] ?? null, \json_encode($family));

        $attributeFilters = $this->attributeFilters($filters);
        $attributeCodes = \array_column($attributeFilters, 'code');
        Assert::assertContains('sku', $attributeCodes);
        Assert::assertNotContains('a_date', $attributeCodes, 'a_date is not useable as a grid filter');
        foreach ($attributeFilters as $attributeFilter) {
            // The keys read by product-grid-filters.ts and simple-select-async.js.
            Assert::assertIsString($attributeFilter['code'] ?? null, \json_encode($attributeFilter));
            Assert::assertIsArray($attributeFilter['labels'] ?? null, \json_encode($attributeFilter));
            Assert::assertIsString($attributeFilter['group'] ?? null, \json_encode($attributeFilter));
        }
        Assert::assertSame(
            'attributeGroupA',
            $attributeFilters[\array_search('sku', $attributeCodes, true)]['group']
        );
    }

    public function test_it_returns_the_requested_attributes_for_init_selection(): void
    {
        $filters = $this->fetchFilters([
            'identifiers' => 'sku,family,a_date',
            'options' => ['limit' => '100'],
        ]);

        // a_date is requested but is not useable as a grid filter; family is not an attribute.
        Assert::assertSame(['sku'], \array_column($this->attributeFilters($filters), 'code'));
        // System filters are not narrowed by identifiers: the front end picks the requested ones.
        Assert::assertContains('family', \array_column($this->systemFilters($filters), 'code'));
    }

    public function test_it_filters_system_filters_and_attributes_by_search_term(): void
    {
        $filters = $this->fetchFilters([
            'search' => 'sku',
            'options' => ['limit' => '20', 'page' => '1', 'catalogLocale' => 'en_US'],
        ]);

        $attributeCodes = \array_column($this->attributeFilters($filters), 'code');
        Assert::assertContains('sku', $attributeCodes);
        Assert::assertNotContains('a_date', $attributeCodes);

        $systemCodes = \array_column($this->systemFilters($filters), 'code');
        Assert::assertNotContains('family', $systemCodes);
        Assert::assertNotContains('enabled', $systemCodes);

        // The absences above also hold if system filters never match a search, so pin both positive branches.
        // By code: 'fam' is part of the family code.
        $systemCodes = \array_column($this->systemFilters($this->fetchFilters([
            'search' => 'fam',
            'options' => ['limit' => '20', 'page' => '1', 'catalogLocale' => 'en_US'],
        ])), 'code');
        Assert::assertContains('family', $systemCodes);
        Assert::assertNotContains('enabled', $systemCodes);

        // By translated label only: the enabled filter is labelled "Status" (datagrid/product.yml) and its code
        // does not contain "stat".
        $systemCodes = \array_column($this->systemFilters($this->fetchFilters([
            'search' => 'Stat',
            'options' => ['limit' => '20', 'page' => '1', 'catalogLocale' => 'en_US'],
        ])), 'code');
        Assert::assertContains('enabled', $systemCodes);
        Assert::assertNotContains('family', $systemCodes);
    }

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($this->get('akeneo_integration_tests.catalogs')->useTechnicalCatalog());

        $this->get('akeneo_integration_tests.security.system_user_authenticator')->createSystemUser();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        $this->webClientHelper = $this->get('akeneo_integration_tests.helper.web_client');
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin', $this->client);
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return list<array<string, mixed>>
     */
    private function fetchFilters(array $parameters): array
    {
        $this->webClientHelper->callApiRoute(
            $this->client,
            'pim_enrich_product_grid_filters',
            [],
            Request::METHOD_GET,
            $parameters
        );
        $response = $this->client->getResponse();
        $this->webClientHelper->assertStatusCode($response, Response::HTTP_OK);

        $filters = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertIsArray($filters, (string) $response->getContent());
        Assert::assertTrue(\array_is_list($filters), (string) $response->getContent());

        return $filters;
    }

    /**
     * @param list<array<string, mixed>> $filters
     *
     * @return list<array<string, mixed>>
     */
    private function systemFilters(array $filters): array
    {
        return \array_values(\array_filter($filters, fn (array $filter): bool => 'system' === ($filter['group'] ?? null)));
    }

    /**
     * @param list<array<string, mixed>> $filters
     *
     * @return list<array<string, mixed>>
     */
    private function attributeFilters(array $filters): array
    {
        return \array_values(\array_filter($filters, fn (array $filter): bool => 'system' !== ($filter['group'] ?? null)));
    }

    private function get(string $service): mixed
    {
        return self::getContainer()->get($service);
    }
}
