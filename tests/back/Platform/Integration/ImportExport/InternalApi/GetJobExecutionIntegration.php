<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\ImportExport\InternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\DBAL\Connection;
use Oro\Bundle\PimDataGridBundle\tests\Integration\Controller\ControllerIntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Backend guard of the internal API the job execution page polls, replacing
 * tests/back/Platform/EndToEnd/ImportExport/InternalApi/GetJobExecutionEndToEnd.php, which never ran in CI (only
 * *Integration.php files are collected by the PIM_Integration_Test suite of phpunit.xml.dist) and pinned the whole
 * normalized payload, key order and job configuration included, with assertSame.
 *
 * Route pim_enrich_job_execution_rest_get (GET /job-execution/rest/{identifier}) →
 * Akeneo\Platform\Bundle\ImportExportBundle\Controller\InternalApi\JobExecutionController::getAction. The response is
 * read by src/Akeneo/Platform/Job/front/process-tracker/src/feature/hooks/useJobExecution.ts, which refreshes it every
 * second until isJobFinished(), and rendered by pages/JobExecutionDetail.tsx and its SummaryTable/Progress components.
 * Only the fields those components read are asserted here, never their order nor the job instance configuration:
 * - jobInstance.label (page title), jobInstance.type (which download/log ACLs apply), jobInstance.code;
 * - status, the TRANSLATED batch status: Progress.tsx compares it to the literal 'Failed';
 * - isRunning / isStoppable, which drive StopJobAction;
 * - stepExecutions[].{label, status, status_code, summary, startedAt, endedAt, warnings, errors, failures} for
 *   SummaryTable.tsx (startedAt/endedAt only for their presence, they are rendered in the user's timezone);
 * - tracking.{status, currentStep, totalSteps, error, warning, steps[].{stepName, status, isTrackable, hasError,
 *   hasWarning, processedItems, totalItems, duration}} for JobExecutionStatus and Progress;
 * - meta.{logExists, archives, generateZipArchive, id} for the "download log" and "download files" actions.
 *
 * The controller normalizes with the context ['limit_warnings' => 100], which makes StepExecutionNormalizer add the
 * "displayed" summary line; that is asserted too.
 *
 * meta.archives filled with real files, and the downloads themselves, are covered by the sibling
 * tests/back/Platform/Integration/ImportExport/InternalApi/LaunchExportAndDownloadFilesIntegration.php (#442).
 * The job execution tracking query behind the "tracking" key has its own guard,
 * tests/back/Platform/Integration/ImportExport/Repository/InternalApi/GetJobExecutionTrackingIntegration.php, whose
 * SQL fixtures this test mirrors.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetJobExecutionIntegration extends ControllerIntegrationTestCase
{
    private const string ROUTE = 'pim_enrich_job_execution_rest_get';
    private const int UNKNOWN_JOB_EXECUTION_ID = 999999999;
    private const string RESTRICTED_ROLE = 'ROLE_WITHOUT_IMPORT_EXECUTION_SHOW';
    private const string IMPORT_EXECUTION_SHOW_PERMISSION = 'action:pim_importexport_import_execution_show';
    private const string EXPORT_EXECUTION_SHOW_PERMISSION = 'action:pim_importexport_export_execution_show';
    /** Only its basename matters: LogKey rebuilds the archivist key from the job type, job name and execution id. */
    private const string LOG_FILE = '/srv/pim/var/logs/batch/19/batch_guard_log.log';

    private int $importJobExecutionId;
    private int $exportJobExecutionId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importJobExecutionId = $this->givenACompletedImportExecution();
        $this->exportJobExecutionId = $this->givenACompletedExportExecution();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    public function test_it_returns_the_job_execution_the_tracker_page_renders(): void
    {
        $this->logIn('admin');

        $jobExecution = $this->getJobExecution($this->importJobExecutionId);

        Assert::assertSame(
            ['code' => 'csv_product_import', 'label' => 'CSV product import', 'type' => 'import'],
            [
                'code' => $jobExecution['jobInstance']['code'] ?? null,
                'label' => $jobExecution['jobInstance']['label'] ?? null,
                'type' => $jobExecution['jobInstance']['type'] ?? null,
            ]
        );
        // JobExecutionNormalizer translates pim_import_export.batch_status.<value>; Progress.tsx reads that label.
        Assert::assertSame('Completed', $jobExecution['status'] ?? null);
        Assert::assertFalse($jobExecution['isRunning'] ?? null);
        Assert::assertFalse($jobExecution['isStoppable'] ?? null, 'a terminated job cannot be stopped');
        Assert::assertSame([], $jobExecution['failures'] ?? null);

        $this->assertStepExecutions($jobExecution['stepExecutions'] ?? []);
        $this->assertTracking($jobExecution['tracking'] ?? []);

        $meta = $jobExecution['meta'] ?? [];
        // The log file was written in the archivist filesystem at the key LogKey builds.
        Assert::assertTrue($meta['logExists'] ?? null, 'the "download log" action is offered');
        // No archiver ever wrote a file for this execution, so the page offers no download: the archivers only list
        // <type>/<job name>/<id>/<archiver name>, and nothing was written under any of those directories.
        Assert::assertSame([], $meta['archives'] ?? null);
        Assert::assertFalse($meta['generateZipArchive'] ?? null);
        // The controller echoes the raw route parameter, which is a string.
        Assert::assertSame((string) $this->importJobExecutionId, $meta['id'] ?? null);
    }

    public function test_it_answers_not_found_for_an_unknown_job_execution(): void
    {
        $this->logIn('admin');

        $this->callApiRoute(
            $this->client,
            self::ROUTE,
            ['identifier' => self::UNKNOWN_JOB_EXECUTION_ID],
            Request::METHOD_GET
        );

        $this->assertStatusCode($this->client->getResponse(), Response::HTTP_NOT_FOUND);
    }

    /**
     * JobExecutionController::getAction has no isXmlHttpRequest() guard, and no access_control rule of
     * config/packages/security.yml singles the internal API out: the X-Requested-With header the front sends is not
     * part of the contract. The unreachable EndToEnd test made it look like it was.
     */
    public function test_it_does_not_require_the_xhr_header(): void
    {
        $this->logIn('admin');

        $this->callRoute(
            $this->client,
            self::ROUTE,
            ['identifier' => $this->importJobExecutionId],
            Request::METHOD_GET
        );

        $this->assertStatusCode($this->client->getResponse(), Response::HTTP_OK);
    }

    /**
     * The ACL depends on the type of the job instance: JobExecutionController is built with the mapping
     * {import: pim_importexport_import_execution_show, export: pim_importexport_export_execution_show}
     * (src/Akeneo/Platform/Bundle/ImportExportBundle/Resources/config/controllers.yml).
     */
    public function test_it_denies_an_import_execution_to_a_user_without_the_import_execution_show_permission(): void
    {
        $this->createUserWithoutTheImportExecutionShowPermission('no_import_execution_show');
        $this->logIn('no_import_execution_show');

        // Positive control: only the import permission was taken away.
        $this->assertActionAclIsGranted('no_import_execution_show', 'pim_importexport_export_execution_show', true);
        $this->assertActionAclIsGranted('no_import_execution_show', 'pim_importexport_import_execution_show', false);

        $this->callApiRoute(
            $this->client,
            self::ROUTE,
            ['identifier' => $this->importJobExecutionId],
            Request::METHOD_GET
        );
        $this->assertStatusCode($this->client->getResponse(), Response::HTTP_FORBIDDEN);

        // Same user, an export execution: the export permission it kept is the one that is checked.
        $exportExecution = $this->getJobExecution($this->exportJobExecutionId);
        Assert::assertSame('export', $exportExecution['jobInstance']['type'] ?? null);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @return array<string, mixed>
     */
    private function getJobExecution(int $jobExecutionId): array
    {
        $this->callApiRoute($this->client, self::ROUTE, ['identifier' => $jobExecutionId], Request::METHOD_GET);
        $response = $this->client->getResponse();
        $this->assertStatusCode($response, Response::HTTP_OK);

        $content = \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertIsArray($content, (string) $response->getContent());

        return $content;
    }

    /**
     * @param list<array<string, mixed>> $stepExecutions
     */
    private function assertStepExecutions(array $stepExecutions): void
    {
        $steps = [];
        foreach ($stepExecutions as $stepExecution) {
            $this->assertHasKeys(
                ['label', 'job', 'status', 'status_code', 'summary', 'startedAt', 'endedAt', 'warnings', 'errors', 'failures'],
                $stepExecution,
                'SummaryTable.tsx reads every one of these keys'
            );
            Assert::assertSame('csv_product_import', $stepExecution['job']);
            Assert::assertSame('Completed', $stepExecution['status']);
            Assert::assertSame('COMPLETED', $stepExecution['status_code']);
            Assert::assertSame([], $stepExecution['errors']);
            Assert::assertSame([], $stepExecution['failures']);
            // Rendered in the timezone of the user, so only their presence is part of the contract.
            Assert::assertNotEmpty($stepExecution['startedAt'], 'the start date is rendered as a column');
            Assert::assertNotEmpty($stepExecution['endedAt'], 'the end date is rendered as a column');
            $steps[$stepExecution['label']] = $stepExecution;
        }
        Assert::assertEqualsCanonicalizing(
            ['validation', 'import', 'import_associations'],
            \array_keys($steps),
            'only the executed steps are listed, the tracking lists the others'
        );

        // Summary keys go through the job_execution.summary.* catalog, values through the translator when they are
        // strings; "first warnings displayed" is the line the ['limit_warnings' => 100] context of the controller adds.
        $this->assertSummary(
            ['File encoding:' => 'UTF-8 OK', 'first warnings displayed' => '1/1'],
            $steps['validation']['summary']
        );
        $this->assertSummary(
            ['read lines' => 38, 'skipped product (no differences)' => 37, 'skipped' => 1],
            $steps['import']['summary']
        );
        Assert::assertSame([], $steps['import_associations']['summary']);

        Assert::assertSame(
            [['reason' => 'The value is not a valid number', 'item' => ['sku' => 'guard-sku']]],
            $steps['validation']['warnings']
        );
        Assert::assertSame([], $steps['import']['warnings']);
    }

    /**
     * @param array<string, mixed> $tracking
     */
    private function assertTracking(array $tracking): void
    {
        Assert::assertSame('COMPLETED', $tracking['status'] ?? null);
        // The job declares more steps than this execution ran: GetJobExecutionTracking counts the step executions as
        // the current step and the steps of the registered job as the total.
        Assert::assertSame(3, $tracking['currentStep'] ?? null);
        Assert::assertSame(
            \count($tracking['steps'] ?? []),
            $tracking['totalSteps'] ?? null,
            'every declared step is tracked, executed or not'
        );
        Assert::assertFalse($tracking['error'] ?? null, 'no step execution carries an error or a failure');
        Assert::assertTrue($tracking['warning'] ?? null, 'the validation step carries one warning');

        $steps = [];
        foreach ($tracking['steps'] ?? [] as $step) {
            $this->assertHasKeys(
                ['jobName', 'stepName', 'status', 'isTrackable', 'hasWarning', 'hasError', 'duration', 'processedItems', 'totalItems'],
                $step,
                'Progress.tsx reads every one of these keys'
            );
            Assert::assertSame('csv_product_import', $step['jobName']);
            $steps[$step['stepName']] = $step;
        }
        Assert::assertSame(
            ['validation', 'import', 'import_associations'],
            \array_values(\array_intersect(\array_keys($steps), ['validation', 'import', 'import_associations'])),
            'the executed steps are tracked in the order the job declares them'
        );

        Assert::assertSame('COMPLETED', $steps['validation']['status']);
        Assert::assertTrue($steps['validation']['hasWarning']);
        Assert::assertFalse($steps['validation']['hasError']);
        // end_time - start_time of the fixture, which is why the frozen clock of GetJobExecutionTrackingIntegration
        // is not needed here.
        Assert::assertSame(5, $steps['validation']['duration']);

        Assert::assertSame('COMPLETED', $steps['import']['status']);
        Assert::assertTrue($steps['import']['isTrackable'], 'the import step implements TrackableStepInterface');
        Assert::assertSame(10, $steps['import']['processedItems']);
        Assert::assertSame(100, $steps['import']['totalItems']);
        Assert::assertSame(14, $steps['import']['duration']);

        Assert::assertSame('COMPLETED', $steps['import_associations']['status']);

        $notExecuted = \array_diff(\array_keys($steps), ['validation', 'import', 'import_associations']);
        Assert::assertNotSame([], $notExecuted, 'csv_product_import declares a step this execution did not run');
        foreach ($notExecuted as $stepName) {
            Assert::assertSame('STARTING', $steps[$stepName]['status']);
            Assert::assertSame(0, $steps[$stepName]['duration']);
        }
    }

    /**
     * Checks that the keys the front reads are all there, without pinning the whole key set: a key added later to the
     * payload is not a regression.
     *
     * @param list<string>         $keys
     * @param array<string, mixed> $actual
     */
    private function assertHasKeys(array $keys, array $actual, string $message): void
    {
        foreach ($keys as $key) {
            Assert::assertArrayHasKey($key, $actual, $message);
        }
    }

    /**
     * Compares a normalized summary without pinning the order of its lines.
     *
     * @param array<string, int|string> $expected
     * @param array<string, int|string> $actual
     */
    private function assertSummary(array $expected, array $actual): void
    {
        Assert::assertEqualsCanonicalizing(\array_keys($expected), \array_keys($actual), \json_encode($actual));
        foreach ($expected as $label => $value) {
            Assert::assertSame($value, $actual[$label] ?? null, $label);
        }
    }

    /**
     * Same fixture as GetJobExecutionTrackingIntegration::thereIsAJobTerminated, plus a warning and a log file: three
     * of the steps csv_product_import declares ran, the first of them raised one warning, and the log file was
     * archived.
     */
    private function givenACompletedImportExecution(): int
    {
        $jobExecutionId = $this->insertJobExecution('csv_product_import', self::LOG_FILE);

        $this->connection()->executeStatement(
            <<<'SQL'
            INSERT INTO `akeneo_batch_step_execution`
                (`job_execution_id`, `step_name`, `status`, `read_count`, `write_count`, `filter_count`, `start_time`,
                 `end_time`, `exit_code`, `exit_description`, `terminate_only`, `failure_exceptions`, `errors`,
                 `summary`, `tracking_data`)
            VALUES
                (:job_execution_id, 'validation', 1, 0, 0, 0, '2020-10-13 13:05:50', '2020-10-13 13:05:55', 'COMPLETED',
                 '', 0, 'a:0:{}', 'a:0:{}', 'a:1:{s:23:"charset_validator.title";s:8:"UTF-8 OK";}',
                 '{"processedItems": 0, "totalItems": 0}')
            SQL,
            ['job_execution_id' => $jobExecutionId]
        );
        $validationStepExecutionId = (int) $this->connection()->lastInsertId();

        $this->connection()->executeStatement(
            <<<'SQL'
            INSERT INTO `akeneo_batch_step_execution`
                (`job_execution_id`, `step_name`, `status`, `read_count`, `write_count`, `filter_count`, `start_time`,
                 `end_time`, `exit_code`, `exit_description`, `terminate_only`, `failure_exceptions`, `errors`,
                 `summary`, `tracking_data`)
            VALUES
                (:job_execution_id, 'import', 1, 0, 0, 0, '2020-10-13 13:05:55', '2020-10-13 13:06:09', 'COMPLETED',
                 '', 0, 'a:0:{}', 'a:0:{}',
                 'a:3:{s:13:"item_position";i:38;s:23:"product_skipped_no_diff";i:37;s:4:"skip";i:1;}',
                 '{"processedItems": 10, "totalItems": 100}'),
                (:job_execution_id, 'import_associations', 1, 0, 0, 0, '2020-10-13 13:06:09', '2020-10-13 13:06:10',
                 'COMPLETED', '', 0, 'a:0:{}', 'a:0:{}', 'a:0:{}', '{"processedItems": 0, "totalItems": 0}')
            SQL,
            ['job_execution_id' => $jobExecutionId]
        );

        // reason_parameters and item are Doctrine "array" columns, so they are stored serialized.
        $this->connection()->executeStatement(
            <<<'SQL'
            INSERT INTO `akeneo_batch_warning` (`step_execution_id`, `reason`, `reason_parameters`, `item`)
            VALUES (:step_execution_id, 'The value is not a valid number', 'a:0:{}',
                    'a:1:{s:3:"sku";s:9:"guard-sku";}')
            SQL,
            ['step_execution_id' => $validationStepExecutionId]
        );

        $this->get('oneup_flysystem.archivist_filesystem')->write(
            \sprintf('import/csv_product_import/%d/log/%s', $jobExecutionId, \basename(self::LOG_FILE)),
            'the batch log'
        );

        return $jobExecutionId;
    }

    /**
     * No step execution and no log file: this one only exists to check which ACL the controller applies to an export.
     */
    private function givenACompletedExportExecution(): int
    {
        return $this->insertJobExecution('csv_product_export', '');
    }

    private function insertJobExecution(string $jobInstanceCode, string $logFile): int
    {
        $jobInstanceId = $this->connection()->fetchOne(
            'SELECT id FROM akeneo_batch_job_instance WHERE code = :code',
            ['code' => $jobInstanceCode]
        );
        Assert::assertNotFalse(
            $jobInstanceId,
            \sprintf('The technical catalog must provide the job instance "%s"', $jobInstanceCode)
        );

        $this->connection()->executeStatement(
            <<<'SQL'
            INSERT INTO `akeneo_batch_job_execution`
                (`job_instance_id`, `pid`, `user`, `status`, `start_time`, `end_time`, `create_time`, `updated_time`,
                 `health_check_time`, `exit_code`, `exit_description`, `failure_exceptions`, `log_file`,
                 `raw_parameters`)
            VALUES
                (:job_instance_id, 86472, 'admin', 1, '2020-10-13 13:05:49', '2020-10-13 13:06:10',
                 '2020-10-13 13:05:45', '2020-10-13 13:06:09', '2020-10-13 13:06:09', 'COMPLETED', '', 'a:0:{}',
                 :log_file, '{}')
            SQL,
            ['job_instance_id' => (int) $jobInstanceId, 'log_file' => $logFile]
        );

        return (int) $this->connection()->lastInsertId();
    }

    /**
     * Mirrors ProductGridCategoryTreeControllerIntegration::createUserWithoutTheCategoryListPermission: a role with
     * the permissions of the administrator, minus the single one under test.
     */
    private function createUserWithoutTheImportExecutionShowPermission(string $username): void
    {
        $role = $this->get('pim_user.factory.role')->create();
        $role->setRole(self::RESTRICTED_ROLE);
        $role->setLabel('Without import execution show');
        $this->get('pim_user.saver.role')->save($role);

        $roleWithPermissionsRepository = $this->get('pim_user.repository.role_with_permissions');
        $permissions = $roleWithPermissionsRepository->findOneByIdentifier('ROLE_ADMINISTRATOR')->permissions();
        Assert::assertTrue(
            $permissions[self::IMPORT_EXECUTION_SHOW_PERMISSION] ?? false,
            'the administrator must be granted the import execution show permission'
        );
        Assert::assertTrue(
            $permissions[self::EXPORT_EXECUTION_SHOW_PERMISSION] ?? false,
            'the administrator must be granted the export execution show permission'
        );
        $permissions[self::IMPORT_EXECUTION_SHOW_PERMISSION] = false;
        $restrictedRole = $roleWithPermissionsRepository->findOneByIdentifier(self::RESTRICTED_ROLE);
        $restrictedRole->setPermissions($permissions);
        $this->get('pim_user.saver.role_with_permissions')->saveAll([$restrictedRole]);

        $aclManager = $this->get('oro_security.acl.manager');
        $aclManager->flush();
        $aclManager->clearCache();

        $user = $this->get('pim_user.factory.user')->create();
        $user->setId(\uniqid());
        $user->setUsername($username);
        $user->setEmail(\sprintf('%s@example.com', \uniqid()));
        $user->setPassword('fake');
        foreach ($this->get('pim_user.repository.group')->findAll() as $group) {
            $user->addGroup($group);
        }
        $user->addRole($this->get('pim_user.repository.role')->findOneByIdentifier(self::RESTRICTED_ROLE));
        // The factory adds ROLE_USER, whose fixture ACL grants every permission (root ACE): remove it, as
        // TestCase::createAdminUser does, or it would grant the import execution show permission back.
        $user->removeRole($this->get('pim_user.repository.role')->findOneByIdentifier(User::ROLE_DEFAULT));
        $this->get('pim_user.saver.user')->save($user);

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        Assert::assertSame(
            [self::RESTRICTED_ROLE],
            $this->get('pim_user.repository.user')->findOneByIdentifier($username)->getRoles()
        );
    }

    /**
     * Same check as the controller's SecurityFacade::isGranted, with a token built from the user's saved roles.
     */
    private function assertActionAclIsGranted(string $username, string $acl, bool $expected): void
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());

        Assert::assertSame(
            $expected,
            $this->get('security.access.decision_manager')->decide($token, ['EXECUTE'], new ObjectIdentity('action', $acl)),
            \sprintf('The ACL "%s" of the user "%s" must be %s', $acl, $username, $expected ? 'granted' : 'denied')
        );
    }

    private function connection(): Connection
    {
        return $this->get('database_connection');
    }
}
