<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Manager;

use Akeneo\Pim\Structure\Bundle\Manager\AttributeCodeBlacklister;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeCodeBlacklisterTest extends TestCase
{
    private Connection|MockObject $connection;
    private AttributeCodeBlacklister $sut;

    /**
     * @var array<int, array{sql: string, params: array<string, mixed>, types: array<string, mixed>}>
     */
    private array $executedStatements = [];

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->executedStatements = [];

        $this->connection->method('executeStatement')->willReturnCallback(
            function (string $sql, array $params = [], array $types = []): int {
                $this->executedStatements[] = ['sql' => $sql, 'params' => $params, 'types' => $types];

                return 1;
            },
        );

        $this->sut = new AttributeCodeBlacklister($this->connection);
    }

    public function test_it_does_nothing_when_blacklisting_no_attribute_codes(): void
    {
        $this->sut->blacklist([]);

        $this->assertCount(0, $this->executedStatements);
    }

    public function test_it_blacklists_the_given_attribute_codes(): void
    {
        $this->sut->blacklist(['sku', 'name']);

        $this->assertCount(1, $this->executedStatements);
        $query = $this->executedStatements[0];
        $this->assertStringContainsString('INSERT INTO `pim_catalog_attribute_blacklist`', $query['sql']);
        $this->assertStringContainsString('(:attribute_code_0),(:attribute_code_1)', $query['sql']);
        $this->assertSame(['attribute_code_0' => 'sku', 'attribute_code_1' => 'name'], $query['params']);
    }

    public function test_it_does_nothing_when_registering_a_job_for_no_attribute_codes(): void
    {
        $this->sut->registerJob([], 42);

        $this->assertCount(0, $this->executedStatements);
    }

    public function test_it_registers_the_job_execution_for_the_given_attribute_codes(): void
    {
        $this->sut->registerJob(['sku'], 42);

        $this->assertCount(1, $this->executedStatements);
        $query = $this->executedStatements[0];
        $this->assertStringContainsString('UPDATE `pim_catalog_attribute_blacklist`', $query['sql']);
        $this->assertStringContainsString('SET `cleanup_job_execution_id` = :job_execution_id', $query['sql']);
        $this->assertSame(['attribute_codes' => ['sku'], 'job_execution_id' => 42], $query['params']);
        $this->assertSame(
            ['attribute_codes' => ArrayParameterType::STRING, 'job_execution_id' => ParameterType::INTEGER],
            $query['types'],
        );
    }

    public function test_it_does_nothing_when_removing_no_attribute_codes_from_the_blacklist(): void
    {
        $this->sut->removeFromBlacklist([]);

        $this->assertCount(0, $this->executedStatements);
    }

    public function test_it_removes_the_given_attribute_codes_from_the_blacklist(): void
    {
        $this->sut->removeFromBlacklist(['sku']);

        $this->assertCount(1, $this->executedStatements);
        $query = $this->executedStatements[0];
        $this->assertStringContainsString('DELETE FROM `pim_catalog_attribute_blacklist`', $query['sql']);
        $this->assertSame(['attribute_codes' => ['sku']], $query['params']);
        $this->assertSame(['attribute_codes' => ArrayParameterType::STRING], $query['types']);
    }
}
