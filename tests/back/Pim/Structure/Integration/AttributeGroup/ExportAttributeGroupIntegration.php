<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\AttributeGroup;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;

/**
 * Back-end guard of the attribute group CSV export, previously covered only by the Behat scenario
 * export_attribute_groups_csv.feature, which exported the footwear attribute groups and checked the file. Behat
 * checked the header as a set and that each expected row's values appear in a row; this test pins the exact lines
 * (only the row order is sorted away, as the database reader uses findAll() without ORDER BY).
 *
 * Same footwear fixtures as Behat (Catalog::useFunctionalCatalog) and the same job instance,
 * csv_footwear_attribute_group_export (footwear jobs.yml). JobLauncher::launchExport forces local storage.
 *
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ExportAttributeGroupIntegration extends TestCase
{
    private const JOB_CODE = 'csv_footwear_attribute_group_export';

    private JobLauncher $jobLauncher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
    }

    public function test_it_exports_attribute_groups_in_csv(): void
    {
        $csv = $this->jobLauncher->launchExport(self::JOB_CODE, null, []);

        self::assertStringEndsWith("\n", $csv, "The CSV must end with a newline:\n" . $csv);
        self::assertStringEndsNotWith("\n\n", $csv, "The CSV must end with a single newline:\n" . $csv);
        $lines = explode("\n", substr($csv, 0, -1));

        // code and label-* first (DefaultColumnSorter ['code', 'label']), then attributes and sort_order natcasesorted.
        self::assertSame('code;label-en_US;attributes;sort_order', array_shift($lines), $csv);

        // Copied from the Behat scenario. The attribute order inside a row is the fixture insertion order
        // (AttributeRepository::getAttributeCodesByGroup), as Behat relied on too.
        $expectedLines = [
            'info;"Product information";sku,name,manufacturer,weather_conditions,description,length,volume,weight;1',
            'marketing;Marketing;price,rating,rate_sale;2',
            'sizes;Sizes;size;3',
            'colors;Colors;color,lace_color;4',
            'media;Media;side_view,top_view,rear_view;5',
            'other;Other;comment,number_in_stock,destocking_date,handmade,heel_color,sole_color,cap_color,sole_fabric,lace_fabric,123;100',
        ];
        sort($expectedLines);
        sort($lines);
        self::assertSame($expectedLines, $lines, $csv);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('footwear');
    }
}
