<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\AttributeGroup\Job;

use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend guard for the attribute groups bulk delete, previously covered by the Behat scenario
 * bulk_delete_attribute_groups.feature:6. The unit tests of MassDeleteAttributeGroupsController, MoveChildAttributesTasklet
 * and DeleteAttributeGroupsTasklet mock the job launcher and AttributeRepository::getAttributesByGroups, so this test
 * runs the real flow:
 * - POST /rest/attribute-group/mass-delete (attribute_group.yml, MassDeleteAttributeGroupsController), sent the way
 *   useMassDeleteAttributeGroups.ts sends it;
 * - the delete_attribute_groups job with its move_child_attributes then delete_attribute_groups steps (jobs.yml,
 *   steps.yml);
 * - the refusal to delete the 'other' group (CheckAttributeGroupOtherCannotBeRemovedSubscriber), reported as a warning;
 * - the notification of the user who launched it (ImportExportBundle NotificationFactory for attribute_group_mass_delete).
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MassDeleteAttributeGroupsIntegration extends WebTestCase
{
    private const string JOB_CODE = 'delete_attribute_groups';
    private const string USERNAME = 'admin';

    private KernelBrowser $client;

    public function test_it_moves_the_attributes_to_other_and_deletes_the_groups_except_other(): void
    {
        $this->createAttributeGroups(['group_a', 'group_b']);
        $this->createTextAttribute('attribute_in_a', 'group_a');

        $this->launchMassDelete(['codes' => ['group_a', 'group_b', 'other']]);

        $jobExecution = $this->theOnlyJobExecution();
        $rawParameters = json_decode($jobExecution['raw_parameters'], true, 512, JSON_THROW_ON_ERROR);
        $expectedParameters = [
            'filters' => ['codes' => ['group_a', 'group_b', 'other']],
            'replacement_attribute_group_code' => 'other',
            'users_to_notify' => [self::USERNAME],
        ];
        $actualParameters = array_intersect_key($rawParameters, $expectedParameters);
        // raw_parameters is a JSON column: MySQL does not keep the key order.
        ksort($expectedParameters);
        ksort($actualParameters);
        Assert::assertSame(
            $expectedParameters,
            $actualParameters,
            'The controller should default the replacement group to "other".',
        );
        // QueueJobLauncher turns send_email into the email option of the queued message and removes it from the job
        // parameters, which the DeleteAttributeGroupsMassEdit constraint collection would otherwise reject.
        Assert::assertArrayNotHasKey('send_email', $rawParameters);

        $this->get('akeneo_integration_tests.launcher.job_launcher')->launchConsumerUntilQueueIsEmpty();
        $this->get('doctrine.orm.entity_manager')->clear();

        Assert::assertSame(BatchStatus::COMPLETED, $this->jobExecutionStatus((int) $jobExecution['id']));
        $steps = $this->stepExecutions((int) $jobExecution['id']);
        Assert::assertSame(['move_child_attributes', 'delete_attribute_groups'], array_keys($steps));
        // 'other' is part of the selection, so its own sku attribute is moved (into 'other') along with attribute_in_a.
        Assert::assertSame(2, $steps['move_child_attributes']['summary']['moved_attributes'] ?? null);
        Assert::assertSame([], $steps['move_child_attributes']['warnings']);
        Assert::assertSame(2, $steps['delete_attribute_groups']['summary']['deleted_attribute_groups'] ?? null);
        Assert::assertSame(1, $steps['delete_attribute_groups']['summary']['skipped_attribute_groups'] ?? null);
        Assert::assertSame(
            [[
                'reason' => 'pim_enrich.attribute_group.remove.attribute_group_other_cannot_be_removed',
                'item' => ['code' => 'other'],
            ]],
            $steps['delete_attribute_groups']['warnings'],
        );

        Assert::assertNull($this->findAttributeGroup('group_a'));
        Assert::assertNull($this->findAttributeGroup('group_b'));
        Assert::assertNotNull($this->findAttributeGroup('other'));
        Assert::assertSame('other', $this->attributeGroupCodeOf('attribute_in_a'));
        Assert::assertSame('other', $this->attributeGroupCodeOf('sku'));

        $this->assertTheUserIsNotifiedOnce(
            (int) $jobExecution['id'],
            'warning',
            'pim_import_export.notification.attribute_group_mass_delete.warning',
        );
    }

    public function test_it_moves_the_attributes_to_the_chosen_replacement_group(): void
    {
        $this->createAttributeGroups(['group_a', 'group_c']);
        $this->createTextAttribute('attribute_in_a', 'group_a');

        $this->launchMassDelete(['codes' => ['group_a'], 'replacement_attribute_group' => 'group_c']);

        $jobExecution = $this->theOnlyJobExecution();
        $rawParameters = json_decode($jobExecution['raw_parameters'], true, 512, JSON_THROW_ON_ERROR);
        Assert::assertSame(['codes' => ['group_a']], $rawParameters['filters'] ?? null);
        Assert::assertSame('group_c', $rawParameters['replacement_attribute_group_code'] ?? null);

        $this->get('akeneo_integration_tests.launcher.job_launcher')->launchConsumerUntilQueueIsEmpty();
        $this->get('doctrine.orm.entity_manager')->clear();

        Assert::assertSame(BatchStatus::COMPLETED, $this->jobExecutionStatus((int) $jobExecution['id']));
        $steps = $this->stepExecutions((int) $jobExecution['id']);
        Assert::assertSame(1, $steps['move_child_attributes']['summary']['moved_attributes'] ?? null);
        Assert::assertSame(1, $steps['delete_attribute_groups']['summary']['deleted_attribute_groups'] ?? null);
        Assert::assertSame(0, $steps['delete_attribute_groups']['summary']['skipped_attribute_groups'] ?? null);
        Assert::assertSame([], $steps['move_child_attributes']['warnings']);
        Assert::assertSame([], $steps['delete_attribute_groups']['warnings']);

        Assert::assertNull($this->findAttributeGroup('group_a'));
        Assert::assertNotNull($this->findAttributeGroup('group_c'));
        Assert::assertSame('group_c', $this->attributeGroupCodeOf('attribute_in_a'));
        Assert::assertSame('other', $this->attributeGroupCodeOf('sku'), 'Attributes of other groups must not move.');

        $this->assertTheUserIsNotifiedOnce(
            (int) $jobExecution['id'],
            'success',
            'pim_import_export.notification.attribute_group_mass_delete.success',
        );
    }

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->disableReboot();

        // The minimal catalog holds the delete_attribute_groups job instance (installer fixtures minimal/jobs.yml).
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
     * @param array<string, mixed> $body
     */
    private function launchMassDelete(array $body): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn(self::USERNAME, $this->client);
        $this->client->request(
            Request::METHOD_POST,
            $this->get('router')->generate('pim_structure_attributegroup_rest_mass_delete'),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'],
            json_encode($body, JSON_THROW_ON_ERROR),
        );

        $response = $this->client->getResponse();
        Assert::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
            sprintf('Unexpected status code, content: %s', $response->getContent()),
        );
    }

    /**
     * @return array{id: int|string, raw_parameters: string}
     */
    private function theOnlyJobExecution(): array
    {
        $executions = $this->connection()->fetchAllAssociative(
            <<<SQL
            SELECT je.id, je.raw_parameters
            FROM akeneo_batch_job_execution je
            INNER JOIN akeneo_batch_job_instance ji ON ji.id = je.job_instance_id
            WHERE ji.code = :code
            SQL,
            ['code' => self::JOB_CODE],
        );
        Assert::assertCount(1, $executions, sprintf('%s executions: %s', self::JOB_CODE, json_encode($executions)));

        return $executions[0];
    }

    private function jobExecutionStatus(int $jobExecutionId): int
    {
        return (int) $this->connection()->fetchOne(
            'SELECT status FROM akeneo_batch_job_execution WHERE id = :id',
            ['id' => $jobExecutionId],
        );
    }

    /**
     * @return array<string, array{summary: array<string, mixed>, warnings: list<array{reason: string, item: array<mixed>}>}>
     */
    private function stepExecutions(int $jobExecutionId): array
    {
        $rows = $this->connection()->fetchAllAssociative(
            'SELECT id, step_name, summary FROM akeneo_batch_step_execution WHERE job_execution_id = :id ORDER BY id',
            ['id' => $jobExecutionId],
        );

        $steps = [];
        foreach ($rows as $row) {
            $warnings = $this->connection()->fetchAllAssociative(
                'SELECT reason, item FROM akeneo_batch_warning WHERE step_execution_id = :id ORDER BY id',
                ['id' => $row['id']],
            );
            $steps[$row['step_name']] = [
                'summary' => $this->connection()->convertToPHPValue($row['summary'], 'array'),
                'warnings' => array_map(
                    fn (array $warning): array => [
                        'reason' => $warning['reason'],
                        'item' => $this->connection()->convertToPHPValue($warning['item'], 'array'),
                    ],
                    $warnings,
                ),
            ];
        }

        return $steps;
    }

    private function assertTheUserIsNotifiedOnce(int $jobExecutionId, string $type, string $message): void
    {
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
        Assert::assertSame($type, $jobNotifications[0]->getType());
        Assert::assertSame($message, $jobNotifications[0]->getMessage());
        Assert::assertSame(['actionType' => 'attribute_group_mass_delete'], $jobNotifications[0]->getContext());
    }

    /**
     * @param string[] $codes
     */
    private function createAttributeGroups(array $codes): void
    {
        foreach ($codes as $code) {
            $attributeGroup = $this->get('pim_catalog.factory.attribute_group')->create();
            $this->get('pim_catalog.updater.attribute_group')->update($attributeGroup, ['code' => $code]);
            $violations = $this->get('validator')->validate($attributeGroup);
            Assert::assertCount(0, $violations, (string) $violations);
            $this->get('pim_catalog.saver.attribute_group')->save($attributeGroup);
        }
    }

    private function createTextAttribute(string $code, string $groupCode): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update(
            $attribute,
            ['code' => $code, 'type' => 'pim_catalog_text', 'group' => $groupCode],
        );
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations, (string) $violations);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function findAttributeGroup(string $code): ?object
    {
        return $this->get('pim_catalog.repository.attribute_group')->findOneByIdentifier($code);
    }

    private function attributeGroupCodeOf(string $attributeCode): ?string
    {
        return $this->get('pim_catalog.repository.attribute')->findOneByIdentifier($attributeCode)?->getGroup()?->getCode();
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
