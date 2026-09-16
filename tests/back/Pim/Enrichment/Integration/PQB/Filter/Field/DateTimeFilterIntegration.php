<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * @var array
     */
    private $createdDates = [];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createdDates['before_first'] = new \DateTime('2018-05-23 00:00:00', new \DateTimeZone('UTC'));
        $this->createProductWithFollowingUpdatedDate('foo',new \DateTime('2018-05-23 00:01:00', new \DateTimeZone('UTC')));
        $this->createdDates['before_second'] = new \DateTime('2018-05-23 00:02:00', new \DateTimeZone('UTC'));
        $this->createProductWithFollowingUpdatedDate('bar', new \DateTime('2018-05-23 00:03:00', new \DateTimeZone('UTC')));
        $this->createdDates['before_third'] = new \DateTime('2018-05-23 00:04:00', new \DateTimeZone('UTC'));
        $this->createProductWithFollowingUpdatedDate('baz', new \DateTime('2018-05-23 00:05:00', new \DateTimeZone('UTC')));
        $this->createdDates['after_all'] = new \DateTime('2018-05-23 00:06:00', new \DateTimeZone('UTC'));

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    public function testOperatorInferior()
    {
        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, $this->createdDates['before_first']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, $this->createdDates['before_second']]]);
        $this->assert($result, ['foo']);

        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, $this->createdDates['before_third']]]);
        $this->assert($result, ['foo', 'bar']);

        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, $this->createdDates['after_all']]]);
        $this->assert($result, ['foo', 'bar', 'baz']);
    }

    public function testOperatorEquals()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $result = $this->executeFilter([['updated', Operators::EQUALS, $now->format('Y-m-d H:i:s')]]);
        $this->assert($result, []);

        $barProductUpdatedDate = new \DateTime('2018-05-23 00:03:00', new \DateTimeZone('UTC'));
        $result = $this->executeFilter([['updated', Operators::EQUALS, $barProductUpdatedDate]]);
        $this->assert($result, ['bar']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->executeFilter([['updated', Operators::GREATER_THAN, $this->createdDates['before_second']]]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->executeFilter([['updated', Operators::GREATER_THAN, $this->createdDates['before_first']]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['updated', Operators::IS_EMPTY, null]]);
        $this->assert($result, []);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['updated', Operators::IS_NOT_EMPTY, null]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorDifferent()
    {
        $barProduct = $this->get('pim_catalog.repository.product')->findOneByIdentifier('bar');
        $updatedAt = $barProduct->getUpdated();
        $updatedAt->setTimezone(new \DateTimeZone('UTC'));

        $barProductUpdatedDate = new \DateTime('2018-05-23 00:03:00', new \DateTimeZone('UTC'));
        $result = $this->executeFilter([['updated', Operators::NOT_EQUAL, $barProductUpdatedDate]]);
        $this->assert($result, ['foo', 'baz']);

        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $result = $this->executeFilter([['updated', Operators::NOT_EQUAL, $currentDate->format('Y-m-d H:i:s')]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testOperatorBetween()
    {
        $result = $this->executeFilter([['updated', Operators::BETWEEN, [$this->createdDates['before_second'], $this->createdDates['after_all']]]]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->executeFilter([['updated', Operators::BETWEEN, [$this->createdDates['before_second'], $this->createdDates['before_third']]]]);
        $this->assert($result, ['bar']);

        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $result = $this->executeFilter([['updated', Operators::BETWEEN, [$this->createdDates['after_all'], $currentDate]]]);
        $this->assert($result, []);
    }

    public function testOperatorNotBetween()
    {
        $result = $this->executeFilter([['updated', Operators::NOT_BETWEEN, [$this->createdDates['before_second'], $this->createdDates['after_all']]]]);
        $this->assert($result, ['foo']);

        $result = $this->executeFilter([['updated', Operators::NOT_BETWEEN, [$this->createdDates['before_second'], $this->createdDates['before_third']]]]);
        $this->assert($result, ['baz', 'foo']);

        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $result = $this->executeFilter([['updated', Operators::NOT_BETWEEN, [$this->createdDates['after_all'], $currentDate]]]);
        $this->assert($result, ['bar', 'baz', 'foo']);
    }

    public function testFilterWithRelativeDates()
    {
        $this->createProductWithFollowingUpdatedDate('test', new \DateTime('3 minutes ago', new \DateTimeZone('UTC')));
        $this->setUpdateDate('foo', new \DateTime('3 hours ago', new \DateTimeZone('UTC')));
        $this->setUpdateDate('bar', new \DateTime('3 days ago', new \DateTimeZone('UTC')));
        $this->setUpdateDate('baz', new \DateTime('3 months ago', new \DateTimeZone('UTC')));
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, 'now']]);
        $this->assert($result, ['test', 'foo', 'bar', 'baz']);

        $result = $this->executeFilter([['updated', Operators::GREATER_THAN, 'now']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, '-30 minutes']]);
        $this->assert($result, ['foo', 'bar', 'baz']);

        $result = $this->executeFilter([['updated', Operators::GREATER_THAN, '-30 minutes']]);
        $this->assert($result, ['test']);

        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, '-12 hours']]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->executeFilter([['updated', Operators::GREATER_THAN, '-12 hours']]);
        $this->assert($result, ['test', 'foo']);

        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, '-2 days']]);
        $this->assert($result, ['bar', 'baz']);

        $result = $this->executeFilter([['updated', Operators::GREATER_THAN, '-2 days']]);
        $this->assert($result, ['test', 'foo']);

        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, '-2 weeks']]);
        $this->assert($result, ['baz']);

        $result = $this->executeFilter([['updated', Operators::GREATER_THAN, '-2 weeks']]);
        $this->assert($result, ['test', 'foo', 'bar']);

        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, '-2 months']]);
        $this->assert($result, ['baz']);

        $result = $this->executeFilter([['updated', Operators::GREATER_THAN, '-2 months']]);
        $this->assert($result, ['test', 'foo', 'bar']);

        $result = $this->executeFilter([['updated', Operators::LOWER_THAN, '-1 year']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['updated', Operators::GREATER_THAN, '-1 year']]);
        $this->assert($result, ['test', 'foo', 'bar', 'baz']);
    }

    /**
     * "Updated SINCE LAST JOB" is the filter the product export profiles use to export only what changed since the
     * last run (see ExportProductsBySpecificDateIntegration). DateTimeFilter resolves it with
     * JobRepositoryInterface::getLastJobExecution($jobInstance, BatchStatus::COMPLETED), whose DQL filters on
     * `j.status = :status` and orders by `j.startTime DESC`: an execution that failed, was stopped, or is still
     * running must never move the date forward, or the products changed since the last successful run would be
     * silently dropped from the next export. ExportProductsBySpecificDateIntegration only ever has one completed
     * execution, so it does not cover that.
     */
    public function testOperatorSinceLastJob()
    {
        $jobCode = 'since_last_job_datetime_filter';
        $this->createJobInstance($jobCode);

        // No execution at all: the filter adds no clause.
        $result = $this->executeFilter([['updated', Operators::SINCE_LAST_JOB, $jobCode]]);
        $this->assert($result, ['foo', 'bar', 'baz']);

        // A completed execution started before every product was updated.
        $this->createJobExecution($jobCode, BatchStatus::COMPLETED, '2018-05-23 00:00:00');
        $result = $this->executeFilter([['updated', Operators::SINCE_LAST_JOB, $jobCode]]);
        $this->assert($result, ['foo', 'bar', 'baz']);

        // Later executions that never completed: the date stays the one of the completed execution.
        $this->createJobExecution($jobCode, BatchStatus::FAILED, '2018-05-23 00:04:00');
        $this->createJobExecution($jobCode, BatchStatus::STOPPED, '2018-05-23 00:04:30');
        $this->createJobExecution($jobCode, BatchStatus::STARTED, '2018-05-23 00:05:30');
        $result = $this->executeFilter([['updated', Operators::SINCE_LAST_JOB, $jobCode]]);
        $this->assert($result, ['foo', 'bar', 'baz']);

        // Positive control: a completed execution started later does move the date, and it is the most recent
        // completed one that wins, not the first.
        $this->createJobExecution($jobCode, BatchStatus::COMPLETED, '2018-05-23 00:02:00');
        $result = $this->executeFilter([['updated', Operators::SINCE_LAST_JOB, $jobCode]]);
        $this->assert($result, ['bar', 'baz']);
    }

    public function testErrorDataIsMalformedWithEmptyArray()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "updated" expects an array with valid data, should contain 2 strings with the format "yyyy-mm-dd H:i:s".');

        $this->executeFilter([['updated', Operators::BETWEEN, []]]);
    }

    public function testErrorDataIsMalformedWithISODate()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Property "updated" expects a string with the format "yyyy-mm-dd H:i:s" as data, "2016-12-12T00:00:00" given.');

        $this->executeFilter([['updated', Operators::EQUALS, '2016-12-12T00:00:00']]);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(UnsupportedFilterException::class);
        $this->expectExceptionMessage('Filter on property "updated" is not supported or does not support operator "IN CHILDREN"');

        $this->executeFilter([['updated', Operators::IN_CHILDREN_LIST, ['2016-08-29 00:00:01']]]);
    }

    /**
     * We force the updated date in order to avoid to use `sleep` between the creation of products, which is time consuming.
     */
    private function createProductWithFollowingUpdatedDate(string $identifier, \DateTimeInterface $updatedDate): void
    {
        $this->createProduct($identifier, []);
        $this->setUpdateDate($identifier, $updatedDate);
    }

    private function setUpdateDate(string $identifier, \DateTimeInterface $updatedDate)
    {
        $sql = <<<SQL
UPDATE pim_catalog_product
SET updated = :updated_date
WHERE uuid = :uuid
SQL;
        $uuid = $this->getProductUuid($identifier);

        $this->get('database_connection')->executeQuery(
            $sql,
            [
                'uuid' => $uuid->getBytes(),
                'updated_date' => $updatedDate,
            ],
            [
                'updated_date' => Types::DATETIME_MUTABLE
            ]);

        $this->getProductAndAncestorsIndexer()->indexFromProductUuids([$uuid]);
    }

    private function getProductAndAncestorsIndexer(): ProductAndAncestorsIndexer
    {
        return $this->get('akeneo.pim.enrichment.elasticsearch.indexer.product_and_ancestors');
    }

    private function createJobInstance(string $code): void
    {
        $this->get(SqlCreateJobInstance::class)->createJobInstance([
            'code' => $code,
            'label' => $code,
            'job_name' => 'csv_product_export',
            'connector' => 'Akeneo CSV Connector',
            'type' => 'export',
        ]);
    }

    /**
     * The job executions are written directly: only their status and their start time matter here, and
     * DoctrineJobRepository reads them through its own connection.
     *
     * @param string $startTime "Y-m-d H:i:s", read back as UTC (see the UTCDateTimeType of config/packages/doctrine.yml)
     */
    private function createJobExecution(string $jobInstanceCode, int $status, string $startTime): void
    {
        $connection = $this->get('database_connection');
        $jobInstanceId = $connection->fetchOne(
            'SELECT id FROM akeneo_batch_job_instance WHERE code = :code',
            ['code' => $jobInstanceCode]
        );
        $this->assertNotFalse($jobInstanceId, sprintf('The job instance "%s" does not exist', $jobInstanceCode));

        // Only job_instance_id, status and start_time are read by getLastJobExecution; the other columns are
        // nullable or have a default.
        $connection->executeStatement(
            <<<'SQL'
            INSERT INTO `akeneo_batch_job_execution`
                (`job_instance_id`, `user`, `status`, `start_time`, `create_time`, `updated_time`,
                 `failure_exceptions`, `raw_parameters`)
            VALUES
                (:job_instance_id, 'admin', :status, :start_time, :start_time, :start_time, 'a:0:{}', '{}')
            SQL,
            [
                'job_instance_id' => (int) $jobInstanceId,
                'status' => $status,
                'start_time' => $startTime,
            ]
        );
    }
}
