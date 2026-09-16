<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Controller;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend guard of PIM-3420, the deleted Behat scenario display_removed_value_history.feature:8: after an attribute
 * used by a product is deleted from the attributes grid, the product history still has the same versions, and the
 * version that set the attribute still shows its raw code with the values it had. Its Playwright replacement,
 * tests/front/e2e/product/product-history.spec.ts, does not run on backend-only changes.
 *
 * The flow is the UI one: DELETE pim_enrich_attribute_rest_remove (AttributeController::removeAction), then
 * AttributeRemovalSubscriber blacklists the code and launches clean_removed_attribute_job when the kernel terminates
 * (KernelBrowser terminates it after each request), the job removes the values with raw SQL, and the history is read
 * from pim_enrich_product_history_rest_get.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductHistoryAfterAttributeRemovalIntegration extends WebTestCase
{
    private const string PRODUCT_IDENTIFIER = 'boots';
    private const string TEXT_ATTRIBUTE = 'history_name';
    private const string MULTI_SELECT_ATTRIBUTE = 'weather_conditions';
    private const string CLEAN_JOB_CODE = 'clean_removed_attribute_job';

    private KernelBrowser $client;

    public function test_deleting_an_attribute_keeps_the_product_versions_and_the_raw_changes_of_the_attribute(): void
    {
        $uuid = $this->createProductWithTwoVersions();
        // Positive controls, so the checks after the deletion cannot pass on data that was never there.
        Assert::assertTrue($this->hasRawValue($uuid, self::MULTI_SELECT_ATTRIBUTE));
        Assert::assertSame([2, 1], array_column($this->getHistory($uuid), 'version'));

        $this->logIn('admin');
        $response = $this->deleteAttribute(self::MULTI_SELECT_ATTRIBUTE, true);

        Assert::assertSame(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode(),
            sprintf('Unexpected response: %d %s', $response->getStatusCode(), $response->getContent())
        );
        Assert::assertFalse($this->attributeExists(self::MULTI_SELECT_ATTRIBUTE));

        $jobExecutionId = $this->getCleanupJobExecutionId(self::MULTI_SELECT_ATTRIBUTE);
        Assert::assertNotNull(
            $jobExecutionId,
            'The deleted attribute code was not blacklisted with the execution of its cleaning job.'
        );
        Assert::assertSame(self::CLEAN_JOB_CODE, $this->getJobInstanceCode($jobExecutionId));

        $this->get('akeneo_integration_tests.launcher.job_launcher')->launchConsumerUntilQueueIsEmpty();
        // The job ran in another process: forget what this entity manager holds.
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        Assert::assertSame(BatchStatus::COMPLETED, $this->getJobExecutionStatus($jobExecutionId));
        Assert::assertFalse($this->hasRawValue($uuid, self::MULTI_SELECT_ATTRIBUTE));
        Assert::assertTrue($this->hasRawValue($uuid, self::TEXT_ATTRIBUTE), 'Only the deleted attribute values go.');
        Assert::assertSame(
            0,
            (int) $this->connection()->fetchOne(
                'SELECT COUNT(*) FROM pim_catalog_attribute_blacklist WHERE attribute_code = :code',
                ['code' => self::MULTI_SELECT_ATTRIBUTE]
            ),
            'The last step of the job removes the code from the blacklist.'
        );

        $history = $this->getHistory($uuid);
        Assert::assertSame(
            [2, 1],
            array_column($history, 'version'),
            'Deleting an attribute must neither add a version to its products nor remove one.'
        );
        $changeset = $history[0]['changeset'];
        Assert::assertArrayHasKey(self::MULTI_SELECT_ATTRIBUTE, $changeset, json_encode($changeset));
        Assert::assertSame('', $changeset[self::MULTI_SELECT_ATTRIBUTE]['old']);
        Assert::assertEqualsCanonicalizing(
            ['cold', 'snowy'],
            explode(',', (string) $changeset[self::MULTI_SELECT_ATTRIBUTE]['new'])
        );
        Assert::assertSame(['old' => '', 'new' => 'Nice boots'], $changeset[self::TEXT_ATTRIBUTE] ?? null);
    }

    public function test_a_delete_that_is_not_an_xhr_is_rejected_and_keeps_the_attribute(): void
    {
        $this->createAttributes();
        $this->logIn('admin');

        $response = $this->deleteAttribute(self::MULTI_SELECT_ATTRIBUTE, false);

        Assert::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        Assert::assertTrue($this->attributeExists(self::MULTI_SELECT_ATTRIBUTE));
        Assert::assertNull($this->getCleanupJobExecutionId(self::MULTI_SELECT_ATTRIBUTE));
    }

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        $this->get('akeneo_integration_tests.loader.fixtures_loader')
            ->load($this->get('akeneo_integration_tests.catalogs')->useTechnicalCatalog());
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
     * Same steps as the Behat scenario: the product is created (version 1), then its name and its weather conditions
     * are set in one save (version 2).
     */
    private function createProductWithTwoVersions(): string
    {
        $this->createAttributes();
        $this->upsertProduct([]);
        $this->upsertProduct([
            new SetTextValue(self::TEXT_ATTRIBUTE, null, null, 'Nice boots'),
            new SetMultiSelectValue(self::MULTI_SELECT_ATTRIBUTE, null, null, ['cold', 'snowy']),
        ]);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier(self::PRODUCT_IDENTIFIER);
        Assert::assertNotNull($product);

        return $product->getUuid()->toString();
    }

    private function createAttributes(): void
    {
        $attributes = [];
        foreach ([self::TEXT_ATTRIBUTE => 'pim_catalog_text', self::MULTI_SELECT_ATTRIBUTE => 'pim_catalog_multiselect'] as $code => $type) {
            $attribute = $this->get('pim_catalog.factory.attribute')->create();
            $this->get('pim_catalog.updater.attribute')->update($attribute, ['code' => $code, 'type' => $type, 'group' => 'other']);
            $violations = $this->get('validator')->validate($attribute);
            Assert::assertCount(0, $violations, (string) $violations);
            $attributes[] = $attribute;
        }
        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);

        foreach (['cold' => 1, 'snowy' => 2] as $code => $sortOrder) {
            $option = $this->get('pim_catalog.factory.attribute_option')->create();
            $this->get('pim_catalog.updater.attribute_option')->update(
                $option,
                ['attribute' => self::MULTI_SELECT_ATTRIBUTE, 'code' => $code, 'sort_order' => $sortOrder]
            );
            $violations = $this->get('validator')->validate($option);
            Assert::assertCount(0, $violations, (string) $violations);
            $this->get('pim_catalog.saver.attribute_option')->save($option);
        }
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function upsertProduct(array $userIntents): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithIdentifier(
                userId: $this->getUserId('admin'),
                productIdentifier: ProductIdentifier::fromIdentifier(self::PRODUCT_IDENTIFIER),
                userIntents: $userIntents
            )
        );
        // disableReboot() keeps the identifier registered by the previous validation.
        $this->get('pim_catalog.validator.unique_value_set')->reset();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    private function deleteAttribute(string $code, bool $xmlHttpRequest): Response
    {
        $this->client->request(
            Request::METHOD_DELETE,
            $this->get('router')->generate('pim_enrich_attribute_rest_remove', ['code' => $code]),
            [],
            [],
            $xmlHttpRequest ? ['HTTP_X-Requested-With' => 'XMLHttpRequest'] : []
        );

        return $this->client->getResponse();
    }

    /**
     * @return array<int, array<string, mixed>> the normalized versions, newest first
     */
    private function getHistory(string $uuid): array
    {
        $this->logIn('admin');
        $this->client->request(
            Request::METHOD_GET,
            $this->get('router')->generate('pim_enrich_product_history_rest_get', ['entityId' => $uuid]),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());

        return json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    private function hasRawValue(string $uuid, string $attributeCode): bool
    {
        return (bool) $this->connection()->fetchOne(
            'SELECT JSON_CONTAINS_PATH(raw_values, \'one\', :path) FROM pim_catalog_product WHERE uuid = :uuid',
            ['path' => sprintf('$."%s"', $attributeCode), 'uuid' => Uuid::fromString($uuid)->getBytes()]
        );
    }

    private function attributeExists(string $code): bool
    {
        return 1 === (int) $this->connection()->fetchOne(
            'SELECT COUNT(*) FROM pim_catalog_attribute WHERE code = :code',
            ['code' => $code]
        );
    }

    private function getCleanupJobExecutionId(string $attributeCode): ?int
    {
        $id = $this->connection()->fetchOne(
            'SELECT cleanup_job_execution_id FROM pim_catalog_attribute_blacklist WHERE attribute_code = :code',
            ['code' => $attributeCode]
        );

        return false === $id || null === $id ? null : (int) $id;
    }

    private function getJobInstanceCode(int $jobExecutionId): string
    {
        return (string) $this->connection()->fetchOne(
            <<<SQL
            SELECT ji.code
            FROM akeneo_batch_job_execution je
            INNER JOIN akeneo_batch_job_instance ji ON ji.id = je.job_instance_id
            WHERE je.id = :id
            SQL,
            ['id' => $jobExecutionId]
        );
    }

    private function getJobExecutionStatus(int $jobExecutionId): int
    {
        return (int) $this->connection()->fetchOne(
            'SELECT status FROM akeneo_batch_job_execution WHERE id = :id',
            ['id' => $jobExecutionId]
        );
    }

    private function getUserId(string $username): int
    {
        $id = $this->connection()->fetchOne('SELECT id FROM oro_user WHERE username = :username', ['username' => $username]);
        Assert::assertNotFalse($id, sprintf('No user exists with username "%s"', $username));

        return (int) $id;
    }

    private function logIn(string $username): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($username, $this->client);
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
