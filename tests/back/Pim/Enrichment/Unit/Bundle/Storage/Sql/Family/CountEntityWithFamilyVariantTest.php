<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Storage\Sql\Family;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Family\CountEntityWithFamilyVariant;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountEntityWithFamilyVariantTest extends TestCase
{
    private Connection|MockObject $connection;
    private CountEntityWithFamilyVariant $sut;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->sut = new CountEntityWithFamilyVariant($this->connection);
    }

    public function test_it_sums_the_product_and_product_model_counts_belonging_to_the_family_variant(): void
    {
        $familyVariant = $this->createMock(FamilyVariantInterface::class);
        $familyVariant->method('getId')->willReturn(7);

        $this->connection->method('executeQuery')->willReturnCallback(
            function (string $sql, array $params) {
                $this->assertSame(['family_variant_id' => 7], $params);
                $result = $this->createMock(Result::class);
                $result->method('fetchOne')->willReturn(
                    \str_contains($sql, 'pim_catalog_product_model') ? '3' : '5',
                );

                return $result;
            },
        );

        $this->assertSame(8, $this->sut->belongingToFamilyVariant($familyVariant));
    }
}
