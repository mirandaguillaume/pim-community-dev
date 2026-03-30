<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Query\Sql;

use Doctrine\DBAL\Connection;
use Oro\Bundle\PimDataGridBundle\Query\ListAttributesQuery;
use Oro\Bundle\PimDataGridBundle\Query\ListAttributesUseableInProductGrid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListAttributesUseableInProductGridTest extends TestCase
{
    private Connection|MockObject $connection;
    private ListAttributesUseableInProductGrid $sut;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->sut = new ListAttributesUseableInProductGrid($this->connection);
    }

    public function test_it_is_a_list_attributes_useable_in_product_grid_query(): void
    {
        $this->assertInstanceOf(ListAttributesUseableInProductGrid::class, $this->sut);
    }
}
