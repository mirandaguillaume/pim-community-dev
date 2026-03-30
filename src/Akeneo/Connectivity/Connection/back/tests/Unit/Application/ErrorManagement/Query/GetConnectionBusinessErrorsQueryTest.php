<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\ErrorManagement\Query;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Query\GetConnectionBusinessErrorsQuery;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetConnectionBusinessErrorsQueryTest extends TestCase
{
    private GetConnectionBusinessErrorsQuery $sut;

    protected function setUp(): void
    {
        $this->sut = new GetConnectionBusinessErrorsQuery('erp', '2020-01-01');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetConnectionBusinessErrorsQuery::class, $this->sut);
    }

    public function test_it_returns_the_connection_code(): void
    {
        $this->assertSame('erp', $this->sut->connectionCode());
    }

    public function test_it_returns_the_end_date(): void
    {
        $this->assertSame('2020-01-01', $this->sut->endDate());
    }

    public function test_it_returns_null_if_there_is_no_end_date(): void
    {
        $this->sut = new GetConnectionBusinessErrorsQuery('erp', null);
        $this->assertNull($this->sut->endDate());
    }
}
