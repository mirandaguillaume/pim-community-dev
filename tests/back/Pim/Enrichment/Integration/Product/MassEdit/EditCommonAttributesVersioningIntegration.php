<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\MassEdit;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend guard of PIM-1920, the deleted Behat scenario mass_edit_and_update_attribute_history.feature:40: the
 * "Edit attribute values" bulk action sets the value on the selected products, and the history of each of them shows
 * one new version holding the change. Its Playwright replacement, tests/front/e2e/product/product-history.spec.ts, does
 * not run on backend-only changes.
 *
 * The request is the one the bulk action sends: POST pim_enrich_mass_edit_rest_launch (MassEditController,
 * MassOperationConverter, OperationJobLauncher). The queued edit_common_attributes job then runs
 * EditAttributesProcessor and ProductAndProductModelWriter, and the history is read from
 * pim_enrich_product_history_rest_get. Payload shape as in
 * tests/back/Pim/Enrichment/EndToEnd/Product/MassEdit/MassEditAttributeValueOfEntitiesEndToEnd.php.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EditCommonAttributesVersioningIntegration extends WebTestCase
{
    private const string JOB_CODE = 'edit_common_attributes';
    private const string ATTRIBUTE = 'a_text';
    private const string NEW_VALUE = 'cool boots';

    private KernelBrowser $client;

    public function test_a_mass_edit_of_attribute_values_adds_one_version_with_the_change_to_each_edited_product(): void
    {
        // In the technical catalog a_text belongs to familyA and familyA3, a product without family accepts every
        // attribute, and familyA1 has no a_text.
        $editedProducts = [
            'boots' => $this->createProduct('boots', 'familyA'),
            'sandals' => $this->createProduct('sandals', 'familyA3'),
            'sneakers' => $this->createProduct('sneakers', null),
        ];
        $productWithoutTheAttribute = $this->createProduct('watch', 'familyA1');
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $this->logIn('admin');
        foreach ([...$editedProducts, 'watch' => $productWithoutTheAttribute] as $identifier => $uuid) {
            Assert::assertSame([1], array_column($this->getHistory($uuid), 'version'), $identifier);
        }

        $response = $this->launchMassEdit([...array_values($editedProducts), $productWithoutTheAttribute]);
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());

        $this->get('akeneo_integration_tests.launcher.job_launcher')->launchConsumerUntilQueueIsEmpty();
        // The job ran in another process: forget what this entity manager holds.
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        $executions = $this->connection()->fetchAllAssociative(
            <<<SQL
            SELECT je.id, je.status
            FROM akeneo_batch_job_execution je
            INNER JOIN akeneo_batch_job_instance ji ON ji.id = je.job_instance_id
            WHERE ji.code = :code
            SQL,
            ['code' => self::JOB_CODE]
        );
        Assert::assertCount(1, $executions, json_encode($executions));
        Assert::assertSame(BatchStatus::COMPLETED, (int) $executions[0]['status']);

        $summary = $this->connection()->convertToPHPValue(
            $this->connection()->fetchOne(
                'SELECT summary FROM akeneo_batch_step_execution WHERE job_execution_id = :id AND step_name = :step',
                ['id' => $executions[0]['id'], 'step' => 'perform']
            ),
            'array'
        );
        Assert::assertSame(3, $summary['update'] ?? null, json_encode($summary));
        Assert::assertSame(1, $summary['skipped_products'] ?? null, json_encode($summary));

        $this->logIn('admin');
        foreach ($editedProducts as $identifier => $uuid) {
            Assert::assertSame(self::NEW_VALUE, $this->getStoredValue($identifier), $identifier);

            $history = $this->getHistory($uuid);
            Assert::assertSame(
                [2, 1],
                array_column($history, 'version'),
                sprintf('The mass edit must add exactly one version to "%s".', $identifier)
            );
            Assert::assertSame(
                ['old' => '', 'new' => self::NEW_VALUE],
                $history[0]['changeset'][self::ATTRIBUTE] ?? null,
                sprintf('Version 2 of "%s": %s', $identifier, json_encode($history[0]['changeset']))
            );
        }

        Assert::assertNull($this->getStoredValue('watch'));
        Assert::assertSame([1], array_column($this->getHistory($productWithoutTheAttribute), 'version'));
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
     * @param string[] $productUuids
     */
    private function launchMassEdit(array $productUuids): Response
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->get('router')->generate('pim_enrich_mass_edit_rest_launch'),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'],
            json_encode([
                'filters' => [
                    [
                        'field' => 'id',
                        'operator' => Operators::IN_LIST,
                        'value' => array_map(static fn (string $uuid): string => sprintf('product_%s', $uuid), $productUuids),
                        'context' => ['locale' => 'en_US', 'scope' => 'ecommerce'],
                    ],
                ],
                'jobInstanceCode' => self::JOB_CODE,
                'actions' => [
                    [
                        'attribute_channel' => 'ecommerce',
                        'attribute_locale' => 'en_US',
                        'ui_locale' => 'en_US',
                        'normalized_values' => [
                            self::ATTRIBUTE => [['locale' => null, 'scope' => null, 'data' => self::NEW_VALUE]],
                        ],
                    ],
                ],
                'itemsCount' => count($productUuids),
                'familyVariant' => null,
                'operation' => 'edit_common',
            ], JSON_THROW_ON_ERROR)
        );

        return $this->client->getResponse();
    }

    private function createProduct(string $identifier, ?string $familyCode): string
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithIdentifier(
                userId: $this->getUserId('admin'),
                productIdentifier: ProductIdentifier::fromIdentifier($identifier),
                userIntents: null === $familyCode ? [] : [new SetFamily($familyCode)]
            )
        );
        // disableReboot() keeps the identifiers registered by the previous validations.
        $this->get('pim_catalog.validator.unique_value_set')->reset();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
        Assert::assertNotNull($product, sprintf('Product "%s" was not created', $identifier));

        return $product->getUuid()->toString();
    }

    private function getStoredValue(string $identifier): mixed
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
        Assert::assertNotNull($product);

        return $product->getValue(self::ATTRIBUTE)?->getData();
    }

    /**
     * @return array<int, array<string, mixed>> the normalized versions, newest first
     */
    private function getHistory(string $uuid): array
    {
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
