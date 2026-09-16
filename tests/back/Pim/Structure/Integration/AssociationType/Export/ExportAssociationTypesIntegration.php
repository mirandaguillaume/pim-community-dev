<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\AssociationType\Export;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;

/**
 * Back-end guard of the association type CSV export, previously covered only by the Behat scenario
 * export_association_types_csv.feature ("file ... should contain N rows"). Runs csv_association_type_export through
 * akeneo:batch:job: database reader, standard normalizer, StandardToFlat\AssociationType, column sorter, CSV writer.
 *
 * JobLauncher::launchExport forces LOCAL storage, so the job completes with local storage (UploadStep runs without
 * error) and the local file holds the exact CSV. The writer already writes to the local path in that case, so this
 * does not prove that UploadStep's transfer copies anything.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ExportAssociationTypesIntegration extends TestCase
{
    private const JOB_CODE = 'csv_association_type_export';

    private JobLauncher $jobLauncher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');

        // The minimal catalog has no association type export job instance. Same keys as the SimpleCsvExport
        // constraint collection used by this job (Structure job_constraints.yml).
        $this->get(SqlCreateJobInstance::class)->createJobInstance([
            'code' => self::JOB_CODE,
            'label' => 'Test CSV association type export',
            'job_name' => self::JOB_CODE,
            'status' => 0,
            'type' => 'export',
            'raw_parameters' => serialize([
                'storage' => ['type' => 'local', 'file_path' => '/tmp/association_type.csv'],
                'delimiter' => ';',
                'enclosure' => '"',
                'withHeader' => true,
                'users_to_notify' => [],
                'is_user_authenticated' => false,
            ]),
        ]);

        $this->createAssociationType([
            'code' => 'TWO_WAY_TYPE',
            'labels' => ['en_US' => 'Two way', 'fr_FR' => 'Double sens'],
            'is_two_way' => true,
            'is_quantified' => false,
        ]);
        $this->createAssociationType([
            'code' => 'QUANTIFIED_TYPE',
            'labels' => ['en_US' => 'Quantified'],
            'is_two_way' => false,
            'is_quantified' => true,
        ]);
    }

    public function test_it_exports_association_types_to_a_local_csv_file(): void
    {
        $csv = $this->jobLauncher->launchExport(self::JOB_CODE, null, []);

        self::assertStringEndsWith("\n", $csv, "The CSV must end with a newline:\n" . $csv);
        self::assertStringEndsNotWith("\n\n", $csv, "The CSV must end with a single newline:\n" . $csv);
        $lines = explode("\n", substr($csv, 0, -1));

        // DefaultColumnSorter puts code and label-* first (['code', 'label']) and natcasesorts the rest. fr_FR is not
        // activated in the minimal catalog (its only channel has en_US), so TranslationNormalizer drops that label.
        $header = str_getcsv(array_shift($lines), ';', '"', '');
        self::assertSame(['code', 'label-en_US', 'is_quantified', 'is_two_way'], $header, $csv);

        // The database reader uses findAll() without ORDER BY: compare the rows by code.
        $rowsByCode = [];
        foreach ($lines as $line) {
            $row = str_getcsv($line, ';', '"', '');
            self::assertCount(\count($header), $row, "Malformed row \"{$line}\":\n" . $csv);
            self::assertArrayNotHasKey($row[0], $rowsByCode, "Duplicate row for {$row[0]}:\n" . $csv);
            $rowsByCode[$row[0]] = $row;
        }
        ksort($rowsByCode);

        // The 4 association types of the minimal catalog plus the 2 created above; the flags are written as 0/1.
        $expectedRowsByCode = [
            'PACK' => ['PACK', 'Pack', '0', '0'],
            'QUANTIFIED_TYPE' => ['QUANTIFIED_TYPE', 'Quantified', '1', '0'],
            'SUBSTITUTION' => ['SUBSTITUTION', 'Substitution', '0', '0'],
            'TWO_WAY_TYPE' => ['TWO_WAY_TYPE', 'Two way', '0', '1'],
            'UPSELL' => ['UPSELL', 'Upsell', '0', '0'],
            'X_SELL' => ['X_SELL', 'Cross sell', '0', '0'],
        ];
        self::assertSame($expectedRowsByCode, $rowsByCode, $csv);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAssociationType(array $data): void
    {
        $associationType = $this->get('pim_catalog.factory.association_type')->create();
        $this->get('pim_catalog.updater.association_type')->update($associationType, $data);
        $violations = $this->get('validator')->validate($associationType);
        self::assertCount(0, $violations, (string) $violations);
        $this->get('pim_catalog.saver.association_type')->save($associationType);
    }
}
