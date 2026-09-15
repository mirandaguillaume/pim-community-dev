<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Attribute;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend guard for the attribute grid bulk delete, the flow driven end to end by
 * tests/front/e2e/structure/bulk-delete-attributes.spec.ts. That spec runs only when front-end files change, while
 * this suite runs on backend changes, so the PHP and YAML pieces of the flow stay checked on backend-only PRs:
 * - the attribute-grid mass_actions entry (Structure datagrid/attribute.yml) and the attribute_mass_delete mass action
 *   type (PimDataGridBundle mass_actions.yml);
 * - POST /rest/mass_edit/get-filter for the attribute grid (MassEditController::getFilterAction,
 *   OroToPimGridFilterAdapter::adaptAttributeGrid, ItemsCounter::count);
 * - POST /rest/attribute/mass-delete (MassDeleteAttributeController) and the delete_attributes job, step and tasklet
 *   (jobs.yml, steps.yml, DeleteAttributesTasklet);
 * - the notification of the user who launched it (ImportExportBundle NotificationFactory for attribute_mass_delete).
 *
 * The requests are sent the way attribute-mass-delete-action.ts sends them: get-filter is a bodiless POST with the
 * grid selection in the query string, and the launch is a JSON POST of get-filter's filters with only a Content-Type
 * header. Neither sends X-Requested-With.
 */
final class MassDeleteAttributesIntegration extends WebTestCase
{
    private const string GRID_NAME = 'attribute-grid';
    private const string MASS_ACTION_NAME = 'attribute_delete';
    private const string JOB_CODE = 'delete_attributes';
    private const string USERNAME = 'admin';

    private KernelBrowser $client;

    public function test_the_attribute_grid_declares_the_bulk_delete_mass_action(): void
    {
        $massAction = $this->get('oro_datagrid.datagrid.manager')
            ->getConfigurationForGrid(self::GRID_NAME)
            ->offsetGetByPath(sprintf('[mass_actions][%s]', self::MASS_ACTION_NAME));

        Assert::assertIsArray($massAction, 'The attribute grid has no attribute_delete mass action.');
        Assert::assertSame('attribute_mass_delete', $massAction['type'] ?? null);
        Assert::assertSame('pim_enrich_attribute_mass_delete', $massAction['acl_resource'] ?? null);
        Assert::assertTrue(
            self::getContainer()->has('pim_datagrid.extension.mass_action.type.attribute_mass_delete'),
            'No mass action type is registered for attribute_mass_delete.',
        );
    }

    public function test_it_deletes_the_attributes_selected_in_the_grid_and_notifies_the_user(): void
    {
        $selection = ['bulk_delete_a', 'bulk_delete_b'];
        $kept = 'bulk_delete_kept';
        $this->createTextAttributes([...$selection, $kept]);
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn(self::USERNAME, $this->client);

        // attribute-mass-delete-action.ts getMassActionData, with the parameters of mass-action.js getActionParameters.
        $this->client->request(
            Request::METHOD_POST,
            $this->generateUrl('pim_enrich_mass_edit_rest_get_filter', [
                'gridName' => self::GRID_NAME,
                'actionName' => self::MASS_ACTION_NAME,
                'inset' => 1,
                'values' => implode(',', $selection),
                'itemsCount' => count($selection),
            ]),
        );
        $this->assertResponseStatus(Response::HTTP_OK);
        $getFilter = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertSame(
            ['filters' => ['search' => null, 'options' => ['identifiers' => $selection]], 'itemsCount' => 2],
            $getFilter,
        );

        // attribute-mass-delete-action.ts launchJob.
        $this->client->request(
            Request::METHOD_POST,
            $this->generateUrl('pim_structure_launch_mass_delete_attribute'),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['filters' => $getFilter['filters']], JSON_THROW_ON_ERROR),
        );
        $this->assertResponseStatus(Response::HTTP_OK);

        $this->get('akeneo_integration_tests.launcher.job_launcher')->launchConsumerUntilQueueIsEmpty();
        // The job ran outside this entity manager, which still holds the execution as it was launched.
        $this->get('doctrine.orm.entity_manager')->clear();

        Assert::assertSame(
            [$kept],
            $this->connection()->fetchFirstColumn(
                'SELECT code FROM pim_catalog_attribute WHERE code IN (?, ?, ?) ORDER BY code',
                [...$selection, $kept],
            ),
            'Only the selected attributes should be deleted.',
        );

        $executions = $this->connection()->fetchAllAssociative(
            <<<SQL
            SELECT je.id, je.status
            FROM akeneo_batch_job_execution je
            INNER JOIN akeneo_batch_job_instance ji ON ji.id = je.job_instance_id
            WHERE ji.code = :code
            SQL,
            ['code' => self::JOB_CODE],
        );
        Assert::assertCount(1, $executions, sprintf('%s executions: %s', self::JOB_CODE, json_encode($executions)));
        Assert::assertSame(BatchStatus::COMPLETED, (int) $executions[0]['status']);
        $jobExecutionId = (int) $executions[0]['id'];

        $steps = $this->connection()->fetchAllAssociative(
            'SELECT id, step_name, summary FROM akeneo_batch_step_execution WHERE job_execution_id = :id',
            ['id' => $jobExecutionId],
        );
        Assert::assertSame([self::JOB_CODE], array_column($steps, 'step_name'));
        $summary = $this->connection()->convertToPHPValue($steps[0]['summary'], 'array');
        Assert::assertSame(2, $summary['deleted_attributes'] ?? null, json_encode($summary));
        Assert::assertSame(0, $summary['skipped_attributes'] ?? null, json_encode($summary));
        Assert::assertSame(
            0,
            (int) $this->connection()->fetchOne(
                'SELECT COUNT(*) FROM akeneo_batch_warning WHERE step_execution_id = :id',
                ['id' => $steps[0]['id']],
            ),
        );

        // MassDeleteAttributeController sets users_to_notify to the user who launched the job.
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier(self::USERNAME);
        $jobNotifications = array_values(array_filter(
            array_map(
                static fn ($userNotification) => $userNotification->getNotification(),
                $this->get('pim_notification.repository.user_notification')->findBy(['user' => $user]),
            ),
            static fn ($notification): bool => 'akeneo_job_process_tracker_details' === $notification->getRoute()
                && $jobExecutionId === (int) ($notification->getRouteParams()['id'] ?? 0),
        ));
        Assert::assertCount(1, $jobNotifications, sprintf('%s should get one notification for the job.', self::USERNAME));
        Assert::assertSame('success', $jobNotifications[0]->getType());
        Assert::assertSame(
            'pim_import_export.notification.attribute_mass_delete.success',
            $jobNotifications[0]->getMessage(),
        );
        Assert::assertSame(['actionType' => 'attribute_mass_delete'], $jobNotifications[0]->getContext());
    }

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        // The minimal catalog holds the delete_attributes and clean_removed_attribute_job instances. The technical
        // catalog's jobs.yml replaces the minimal one and has no delete_attributes.
        $this->get('akeneo_integration_tests.loader.fixtures_loader')
            ->load($this->get('akeneo_integration_tests.catalogs')->useMinimalCatalog());
        $this->get('akeneo_integration_tests.security.system_user_authenticator')->createSystemUser();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function tearDown(): void
    {
        $this->get('akeneo_integration_tests.doctrine.connection.connection_closer')->closeConnections();

        parent::tearDown();
    }

    /**
     * @param string[] $codes
     */
    private function createTextAttributes(array $codes): void
    {
        $attributes = [];
        foreach ($codes as $code) {
            $attribute = $this->get('pim_catalog.factory.attribute')->create();
            $this->get('pim_catalog.updater.attribute')->update(
                $attribute,
                ['code' => $code, 'type' => 'pim_catalog_text', 'group' => 'other'],
            );
            $violations = $this->get('validator')->validate($attribute);
            Assert::assertCount(0, $violations, (string) $violations);
            $attributes[] = $attribute;
        }

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    private function assertResponseStatus(int $expected): void
    {
        $response = $this->client->getResponse();
        Assert::assertSame(
            $expected,
            $response->getStatusCode(),
            sprintf('Unexpected status code, content: %s', $response->getContent()),
        );
    }

    /**
     * @param array<string, int|string> $parameters
     */
    private function generateUrl(string $route, array $parameters = []): string
    {
        return $this->get('router')->generate($route, $parameters);
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
