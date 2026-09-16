<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\ImportExport\InternalApi;

use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFileValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * Backend guard of the product export flows of the deleted Behat scenarios
 * export_products_and_download_exported_file.feature:7 and export_products_by_specific_date.feature:7: an export is
 * launched from its job page, runs from the job queue, and its generated files are offered on the execution page
 * ("Download generated files" and "Download generated archive"). Their Playwright replacements,
 * tests/front/e2e/export/export-products-and-download.spec.ts and tests/front/e2e/export/export-launch.spec.ts, do not
 * run on backend-only changes. The SINCE LAST JOB filter itself is guarded by ExportProductsBySpecificDateIntegration.
 *
 * Requests, in the UI order:
 * - POST pim_enrich_job_instance_rest_export_launch (JobInstanceController::launchExportAction);
 * - GET pim_enrich_job_execution_rest_get, whose meta.archives and meta.generateZipArchive render the download buttons;
 * - GET pim_enrich_job_tracker_download_file and pim_enrich_job_tracker_download_zip_archive (JobTrackerController).
 *
 * The export uses the "none" storage, the default of a job created from the UI: its files only exist in the job
 * archive. The products and the export profile are the ones of the former GenerateZipArchiveEndToEnd, which never ran
 * in CI and is replaced by this test.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchExportAndDownloadFilesIntegration extends WebTestCase
{
    private const string JOB_CODE = 'csv_product_export_with_media';
    private const string SKU1_UUID = '4168a79a-65b7-418f-b713-ac25b0291131';
    private const string SKU2_UUID = '9f987844-e0c9-4f89-80e0-bdedd597f888';
    private const int UNKNOWN_JOB_EXECUTION_ID = 999999999;

    private KernelBrowser $client;
    private ?string $zipFile = null;

    public function test_an_export_launched_from_its_job_page_generates_files_that_can_be_downloaded(): void
    {
        $this->logIn('admin');
        $response = $this->launchExport(true);

        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        $jobExecutionId = $this->getLastJobExecutionId();
        Assert::assertNotNull($jobExecutionId, 'The launch did not create a job execution.');
        Assert::assertSame(
            ['redirectUrl' => '#' . $this->get('router')->generate('akeneo_job_process_tracker_details', ['id' => $jobExecutionId])],
            json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR)
        );

        $this->get('akeneo_integration_tests.launcher.job_launcher')->launchConsumerUntilQueueIsEmpty();
        // The job ran in another process: forget what this entity manager holds.
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        Assert::assertSame(BatchStatus::COMPLETED, $this->getJobExecutionStatus($jobExecutionId));

        $this->client->request(
            Request::METHOD_GET,
            $this->get('router')->generate('pim_enrich_job_execution_rest_get', ['identifier' => $jobExecutionId]),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
        $response = $this->client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        $meta = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR)['meta'] ?? [];
        Assert::assertSame(['export.csv'], array_keys($meta['archives']['output']['files'] ?? []), json_encode($meta));
        Assert::assertTrue($meta['generateZipArchive'] ?? null, 'The CSV and the media make at least two archives.');

        [$status, $csv] = $this->download(
            'pim_enrich_job_tracker_download_file',
            ['id' => $jobExecutionId, 'archiver' => 'output', 'key' => 'export.csv']
        );
        Assert::assertSame(Response::HTTP_OK, $status, $csv);
        $this->assertExportedProducts($csv);

        [$status, $zipContent] = $this->download(
            'pim_enrich_job_tracker_download_zip_archive',
            ['jobExecutionId' => $jobExecutionId]
        );
        Assert::assertSame(Response::HTTP_OK, $status);
        Assert::assertEqualsCanonicalizing(
            [
                'export.csv',
                'files/sku1/an_image/akeneo.png',
                'files/sku1/a_file/akeneo.pdf',
                'files/sku2/an_image/akeneo.jpg',
            ],
            $this->zipEntries($zipContent)
        );

        // JobTrackerController refuses both downloads to a user who cannot see export profiles.
        $this->revokePermissionFromRole('ROLE_USER', 'pim_importexport_export_profile_show');
        $this->logIn('mary');
        [$status] = $this->download(
            'pim_enrich_job_tracker_download_file',
            ['id' => $jobExecutionId, 'archiver' => 'output', 'key' => 'export.csv']
        );
        Assert::assertSame(Response::HTTP_FORBIDDEN, $status);
        [$status] = $this->download('pim_enrich_job_tracker_download_zip_archive', ['jobExecutionId' => $jobExecutionId]);
        Assert::assertSame(Response::HTTP_FORBIDDEN, $status);

        $this->logIn('admin');
        [$status] = $this->download(
            'pim_enrich_job_tracker_download_file',
            ['id' => self::UNKNOWN_JOB_EXECUTION_ID, 'archiver' => 'output', 'key' => 'export.csv']
        );
        Assert::assertSame(Response::HTTP_NOT_FOUND, $status);
        [$status] = $this->download(
            'pim_enrich_job_tracker_download_zip_archive',
            ['jobExecutionId' => self::UNKNOWN_JOB_EXECUTION_ID]
        );
        Assert::assertSame(Response::HTTP_NOT_FOUND, $status);
    }

    public function test_a_launch_that_is_not_an_xhr_is_redirected_and_launches_nothing(): void
    {
        $this->logIn('admin');

        $response = $this->launchExport(false);

        Assert::assertTrue(
            $response->isRedirect('/'),
            sprintf('Expected a redirect to "/", got: %d %s', $response->getStatusCode(), $response->getContent())
        );
        Assert::assertNull($this->getLastJobExecutionId());
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

        $this->createProduct(self::SKU1_UUID, [
            new SetIdentifierValue('sku', 'sku1'),
            new SetFamily('familyA'),
            new SetCategories(['categoryA']),
            new SetImageValue('an_image', null, null, $this->storeFixture('akeneo.png')),
            new SetFileValue('a_file', null, null, $this->storeFixture('akeneo.pdf')),
        ]);
        $this->createProduct(self::SKU2_UUID, [
            new SetIdentifierValue('sku', 'sku2'),
            new SetFamily('familyA'),
            new SetCategories(['categoryA']),
            new SetImageValue('an_image', null, null, $this->storeFixture('akeneo.jpg')),
        ]);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        // Enabled products of the master tree, with their media, no condition on completeness.
        $this->get(SqlCreateJobInstance::class)->createJobInstance([
            'code' => self::JOB_CODE,
            'label' => self::JOB_CODE,
            'job_name' => 'csv_product_export',
            'connector' => 'Akeneo CSV Connector',
            'status' => 0,
            'type' => 'export',
            'raw_parameters' => 'a:14:{s:7:"storage";a:2:{s:4:"type";s:4:"none";s:9:"file_path";s:15:"/tmp/export.csv";}s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:10:"withHeader";b:1;s:9:"with_uuid";b:1;s:15:"users_to_notify";a:0:{}s:21:"is_user_authenticated";b:0;s:16:"decimalSeparator";s:1:".";s:10:"dateFormat";s:10:"yyyy-MM-dd";s:10:"with_media";b:1;s:10:"with_label";b:0;s:17:"header_with_label";b:0;s:11:"file_locale";N;s:7:"filters";a:2:{s:4:"data";a:3:{i:0;a:3:{s:5:"field";s:7:"enabled";s:8:"operator";s:1:"=";s:5:"value";b:1;}i:1;a:4:{s:5:"field";s:12:"completeness";s:8:"operator";s:3:"ALL";s:5:"value";i:100;s:7:"context";a:1:{s:7:"locales";a:1:{i:0;s:5:"en_US";}}}i:2;a:3:{s:5:"field";s:10:"categories";s:8:"operator";s:11:"IN CHILDREN";s:5:"value";a:1:{i:0;s:6:"master";}}}s:9:"structure";a:3:{s:5:"scope";s:9:"ecommerce";s:7:"locales";a:1:{i:0;s:5:"en_US";}s:10:"attributes";a:2:{i:0;s:8:"an_image";i:1;s:6:"a_file";}}}}',
        ]);
    }

    protected function tearDown(): void
    {
        if (null !== $this->zipFile && file_exists($this->zipFile)) {
            unlink($this->zipFile);
        }
        $this->get('akeneo_integration_tests.doctrine.connection.connection_closer')->closeConnections();

        parent::tearDown();
    }

    private function launchExport(bool $xmlHttpRequest): Response
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->get('router')->generate('pim_enrich_job_instance_rest_export_launch', ['code' => self::JOB_CODE]),
            [],
            [],
            $xmlHttpRequest ? ['HTTP_X-Requested-With' => 'XMLHttpRequest'] : []
        );

        return $this->client->getResponse();
    }

    /**
     * Both download actions answer a streamed response. KernelBrowser sends it into its own output buffer, so the
     * body is read from the internal response.
     *
     * @param array<string, int|string> $parameters
     *
     * @return array{int, string}
     */
    private function download(string $route, array $parameters): array
    {
        $this->client->request(Request::METHOD_GET, $this->get('router')->generate($route, $parameters));

        return [
            $this->client->getResponse()->getStatusCode(),
            (string) $this->client->getInternalResponse()->getContent(),
        ];
    }

    private function assertExportedProducts(string $csv): void
    {
        $lines = explode("\n", trim($csv));
        $header = str_getcsv(array_shift($lines), ';', '"', '');
        Assert::assertSame(['uuid', 'sku'], array_slice($header, 0, 2), $csv);
        Assert::assertEqualsCanonicalizing(
            ['uuid', 'sku', 'categories', 'enabled', 'family', 'groups', 'an_image', 'a_file'],
            $header,
            $csv
        );

        $rows = [];
        foreach ($lines as $line) {
            $row = array_combine($header, str_getcsv($line, ';', '"', ''));
            $rows[$row['uuid']] = $row;
        }
        Assert::assertEqualsCanonicalizing([self::SKU1_UUID, self::SKU2_UUID], array_keys($rows), $csv);

        $expected = [
            self::SKU1_UUID => ['sku1', 'files/sku1/an_image/akeneo.png', 'files/sku1/a_file/akeneo.pdf'],
            self::SKU2_UUID => ['sku2', 'files/sku2/an_image/akeneo.jpg', ''],
        ];
        foreach ($expected as $uuid => [$sku, $image, $file]) {
            $expectedRow = [
                'uuid' => $uuid,
                'sku' => $sku,
                'categories' => 'categoryA',
                'enabled' => '1',
                'family' => 'familyA',
                'groups' => '',
                'an_image' => $image,
                'a_file' => $file,
            ];
            // Compared column by column, the order of the columns is not part of the scenario.
            $actualRow = [];
            foreach (array_keys($expectedRow) as $column) {
                $actualRow[$column] = $rows[$uuid][$column];
            }
            Assert::assertSame($expectedRow, $actualRow, $csv);
        }
    }

    /**
     * @return string[]
     */
    private function zipEntries(string $zipContent): array
    {
        Assert::assertNotSame('', $zipContent, 'The zip archive is empty.');
        $this->zipFile = (string) tempnam(sys_get_temp_dir(), 'export_archive');
        file_put_contents($this->zipFile, $zipContent);

        $zip = new \ZipArchive();
        Assert::assertTrue($zip->open($this->zipFile), 'The downloaded archive is not a zip file.');
        $entries = [];
        for ($index = 0; $index < $zip->count(); $index++) {
            $entries[] = $zip->statIndex($index)['name'] ?? null;
        }
        $zip->close();

        return $entries;
    }

    /**
     * @param UserIntent[] $userIntents
     */
    private function createProduct(string $uuid, array $userIntents): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithUuid(
                $this->getUserId('admin'),
                ProductUuid::fromUuid(Uuid::fromString($uuid)),
                $userIntents
            )
        );
        // disableReboot() keeps the identifiers registered by the previous validations.
        $this->get('pim_catalog.validator.unique_value_set')->reset();
    }

    private function storeFixture(string $fileName): string
    {
        $configuration = $this->get('akeneo_integration_tests.catalogs')->useTechnicalCatalog();
        foreach ($configuration->getFixtureDirectories() as $directory) {
            $path = $directory . DIRECTORY_SEPARATOR . $fileName;
            if (is_file($path)) {
                return $this->get('akeneo_file_storage.file_storage.file.file_storer')
                    ->store(new \SplFileInfo($path), FileStorage::CATALOG_STORAGE_ALIAS)
                    ->getKey();
            }
        }

        throw new \RuntimeException(sprintf('The fixture "%s" does not exist.', $fileName));
    }

    /**
     * Same approach as tests/back/Channel/Integration/ControllerIntegrationTestCase.php.
     */
    private function revokePermissionFromRole(string $roleCode, string $aclId): void
    {
        $role = $this->get('pim_user.repository.role')->findOneByIdentifier($roleCode);
        Assert::assertNotNull($role);

        $aclManager = $this->get('oro_security.acl.manager');
        $sid = $aclManager->getSid($role);
        $revoked = false;
        foreach ($aclManager->getAllExtensions() as $extension) {
            foreach ($extension->getClasses() as $aclClassInfo) {
                if ($aclClassInfo->getClassName() === $aclId) {
                    $oid = new ObjectIdentity($extension->getExtensionKey(), $aclClassInfo->getClassName());
                    $aclManager->setPermission($sid, $oid, AccessLevel::NONE_LEVEL, true);
                    $revoked = true;
                }
            }
        }
        Assert::assertTrue($revoked, sprintf('No ACL named "%s"', $aclId));

        $aclManager->flush();
    }

    private function getLastJobExecutionId(): ?int
    {
        $id = $this->connection()->fetchOne(
            <<<SQL
            SELECT je.id
            FROM akeneo_batch_job_execution je
            INNER JOIN akeneo_batch_job_instance ji ON ji.id = je.job_instance_id
            WHERE ji.code = :code
            ORDER BY je.id DESC
            LIMIT 1
            SQL,
            ['code' => self::JOB_CODE]
        );

        return false === $id ? null : (int) $id;
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
