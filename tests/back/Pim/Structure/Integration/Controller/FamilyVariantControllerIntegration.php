<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Controller;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend guard for the family variant modal of the family Variants tab, previously covered by the Behat scenarios
 * show_family_variant.feature:11 and edit_family_variant.feature:12 (@critical). The service-level family variant tests
 * never go through FamilyVariantController:
 * - GET /configuration/rest/family/family-variant/{identifier} and the internal_api FamilyVariantNormalizer meta that
 *   picks the edit form;
 * - PUT /configuration/rest/family-variant/{identifier} with the body family-variant/form/save.js sends (the fetched
 *   family variant without its meta), the violation response, and the compute_family_variant_structure_changes job the
 *   save launches under the HTTP-authenticated user.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FamilyVariantControllerIntegration extends WebTestCase
{
    private const string FAMILY_VARIANT_CODE = 'clothing_color_size';
    private const string STRUCTURE_CHANGES_JOB = 'compute_family_variant_structure_changes';
    private const string VARIANT_PRODUCT = '1111111270';

    private KernelBrowser $client;

    public function test_it_gets_the_family_variant_with_the_meta_of_its_edit_form(): void
    {
        $familyVariant = $this->fetchFamilyVariant();

        $meta = $familyVariant['meta'];
        unset($familyVariant['meta']);

        // The standard format asserted by Normalizer/Standard/FamilyVariantIntegration.php.
        Assert::assertSame(
            [
                'code' => 'clothing_color_size',
                'labels' => [
                    'de_DE' => 'Kleidung nach Farbe und Größe',
                    'en_US' => 'Clothing by color and size',
                    'fr_FR' => 'Vêtements par couleur et taille',
                ],
                'family' => 'clothing',
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'axes' => ['color'],
                        'attributes' => ['variation_name', 'variation_image', 'composition', 'color', 'material'],
                    ],
                    [
                        'level' => 2,
                        'axes' => ['size'],
                        'attributes' => ['sku', 'weight', 'size', 'ean'],
                    ],
                ],
            ],
            $familyVariant,
        );
        Assert::assertSame('pim-family-variant-edit-form', $meta['form'] ?? null);
        Assert::assertSame('family_variant', $meta['model_type'] ?? null);
        Assert::assertSame($this->familyVariantId(), $meta['id'] ?? null);
        Assert::assertArrayHasKey('structure_version', $meta);
    }

    public function test_it_returns_a_not_found_response_for_an_unknown_family_variant(): void
    {
        $this->logIn();
        $response = $this->callApiRoute(
            'pim_enrich_family_variant_rest_get',
            ['identifier' => 'unknown_family_variant'],
            Request::METHOD_GET,
        );

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $response);
    }

    public function test_removing_an_attribute_from_the_last_level_saves_it_and_clears_its_values_on_the_variant_products(): void
    {
        Assert::assertInstanceOf(ValueInterface::class, $this->weightOfTheVariantProduct());
        $executionsBefore = $this->structureChangesJobExecutionCount();

        $familyVariant = $this->fetchFamilyVariant();
        unset($familyVariant['meta']);
        Assert::assertSame(2, $familyVariant['variant_attribute_sets'][1]['level']);
        $familyVariant['variant_attribute_sets'][1]['attributes'] = array_values(array_diff(
            $familyVariant['variant_attribute_sets'][1]['attributes'],
            ['weight'],
        ));

        $response = $this->putFamilyVariant($familyVariant);

        $this->assertStatusCode(Response::HTTP_OK, $response);
        $savedFamilyVariant = $this->decode($response);
        Assert::assertEqualsCanonicalizing(
            ['sku', 'size', 'ean'],
            $savedFamilyVariant['variant_attribute_sets'][1]['attributes'],
        );
        Assert::assertSame('pim-family-variant-edit-form', $savedFamilyVariant['meta']['form'] ?? null);
        Assert::assertSame(
            $executionsBefore + 1,
            $this->structureChangesJobExecutionCount(),
            'Saving the family variant should launch one compute_family_variant_structure_changes job.',
        );

        $this->get('akeneo_integration_tests.launcher.job_launcher')->launchConsumerUntilQueueIsEmpty();
        $this->get('doctrine.orm.entity_manager')->clear();

        Assert::assertEqualsCanonicalizing(['sku', 'size', 'ean'], $this->attributeCodesOfLevel(2));
        Assert::assertNull(
            $this->weightOfTheVariantProduct(),
            'The weight moved out of the variant level should be removed from the variant product.',
        );
    }

    public function test_it_returns_the_violations_and_saves_nothing_when_the_variant_axes_change(): void
    {
        $executionsBefore = $this->structureChangesJobExecutionCount();

        $familyVariant = $this->fetchFamilyVariant();
        unset($familyVariant['meta']);
        Assert::assertSame(['size'], $familyVariant['variant_attribute_sets'][1]['axes']);
        $familyVariant['variant_attribute_sets'][1]['axes'] = [];

        $response = $this->putFamilyVariant($familyVariant);

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $response);
        $violations = $this->decode($response);
        $axesViolations = array_values(array_filter(
            $violations,
            static fn (array $violation): bool => 'Variant axes cannot be modified for the level "2"' === ($violation['message'] ?? null),
        ));
        // Emptying the axes also raises the "no axis" violation of FamilyVariantValidator, so only this one is checked.
        // ImmutableVariantAxes is reported at variantAttributeSets[1].axes, tableized by the internal_api normalizer.
        Assert::assertSame(
            [['path' => 'variant_attribute_sets[1].axes', 'message' => 'Variant axes cannot be modified for the level "2"', 'global' => false]],
            $axesViolations,
            sprintf('Violations: %s', json_encode($violations)),
        );
        Assert::assertSame($executionsBefore, $this->structureChangesJobExecutionCount());

        $this->get('doctrine.orm.entity_manager')->clear();
        Assert::assertSame(['size'], $this->axisCodesOfLevel(2));
    }

    public function test_it_redirects_a_put_that_is_not_an_xml_http_request(): void
    {
        $this->logIn();
        $this->client->request(
            Request::METHOD_PUT,
            $this->get('router')->generate('pim_enrich_family_variant_rest_put', ['identifier' => self::FAMILY_VARIANT_CODE]),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['labels' => ['en_US' => 'Not saved']], JSON_THROW_ON_ERROR),
        );

        Assert::assertTrue($this->client->getResponse()->isRedirect('/'));
        $this->get('doctrine.orm.entity_manager')->clear();
        Assert::assertSame(
            'Clothing by color and size',
            $this->get('pim_catalog.repository.family_variant')
                ->findOneByIdentifier(self::FAMILY_VARIANT_CODE)
                ->getTranslation('en_US')
                ->getLabel(),
        );
    }

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        // The catalog of the removed Behat scenarios, also used by ChangeVariantFamilyStructureIntegration.
        $this->get('akeneo_integration_tests.loader.fixtures_loader')
            ->load($this->get('akeneo_integration_tests.catalogs')->useFunctionalCatalog('catalog_modeling'));
        $this->get('akeneo_integration_tests.security.system_user_authenticator')->createSystemUser();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $this->get('akeneo_integration_tests.launcher.job_launcher')->flushJobQueue();
    }

    protected function tearDown(): void
    {
        $this->get('akeneo_integration_tests.doctrine.connection.connection_closer')->closeConnections();

        parent::tearDown();
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchFamilyVariant(): array
    {
        $this->logIn();
        $response = $this->callApiRoute(
            'pim_enrich_family_variant_rest_get',
            ['identifier' => self::FAMILY_VARIANT_CODE],
            Request::METHOD_GET,
        );
        $this->assertStatusCode(Response::HTTP_OK, $response);

        return $this->decode($response);
    }

    /**
     * @param array<string, mixed> $familyVariant
     */
    private function putFamilyVariant(array $familyVariant): Response
    {
        return $this->callApiRoute(
            'pim_enrich_family_variant_rest_put',
            ['identifier' => self::FAMILY_VARIANT_CODE],
            Request::METHOD_PUT,
            json_encode($familyVariant, JSON_THROW_ON_ERROR),
        );
    }

    private function weightOfTheVariantProduct(): ?ValueInterface
    {
        return $this->get('pim_catalog.repository.product')
            ->findOneByIdentifier(self::VARIANT_PRODUCT)
            ->getValuesForVariation()
            ->getByCodes('weight');
    }

    /**
     * @return string[]
     */
    private function attributeCodesOfLevel(int $level): array
    {
        return $this->codes(
            $this->get('pim_catalog.repository.family_variant')
                ->findOneByIdentifier(self::FAMILY_VARIANT_CODE)
                ->getVariantAttributeSet($level)
                ->getAttributes()
                ->toArray(),
        );
    }

    /**
     * @return string[]
     */
    private function axisCodesOfLevel(int $level): array
    {
        return $this->codes(
            $this->get('pim_catalog.repository.family_variant')
                ->findOneByIdentifier(self::FAMILY_VARIANT_CODE)
                ->getVariantAttributeSet($level)
                ->getAxes()
                ->toArray(),
        );
    }

    /**
     * @param AttributeInterface[] $attributes
     *
     * @return string[]
     */
    private function codes(array $attributes): array
    {
        return array_values(array_map(static fn (AttributeInterface $attribute): string => $attribute->getCode(), $attributes));
    }

    private function familyVariantId(): int
    {
        return (int) $this->connection()->fetchOne(
            'SELECT id FROM pim_catalog_family_variant WHERE code = :code',
            ['code' => self::FAMILY_VARIANT_CODE],
        );
    }

    private function structureChangesJobExecutionCount(): int
    {
        return (int) $this->connection()->fetchOne(
            <<<SQL
            SELECT COUNT(*)
            FROM akeneo_batch_job_execution je
            INNER JOIN akeneo_batch_job_instance ji ON ji.id = je.job_instance_id
            WHERE ji.code = :code
            SQL,
            ['code' => self::STRUCTURE_CHANGES_JOB],
        );
    }

    private function logIn(): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin', $this->client);
    }

    private function callApiRoute(string $route, array $routeArguments, string $method, ?string $content = null): Response
    {
        $this->client->request(
            $method,
            $this->get('router')->generate($route, $routeArguments),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'],
            $content,
        );

        return $this->client->getResponse();
    }

    /**
     * @return array<mixed>
     */
    private function decode(Response $response): array
    {
        return json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    private function assertStatusCode(int $expected, Response $response): void
    {
        Assert::assertSame(
            $expected,
            $response->getStatusCode(),
            sprintf('Unexpected status code, content: %s', $response->getContent()),
        );
    }

    private function connection(): Connection
    {
        return $this->get('database_connection');
    }

    private function get(string $service): mixed
    {
        return self::getContainer()->get($service);
    }
}
