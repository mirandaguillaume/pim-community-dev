<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\ErrorManagement\Query;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Query\GetConnectionBusinessErrorsHandler;
use Akeneo\Connectivity\Connection\Application\ErrorManagement\Query\GetConnectionBusinessErrorsQuery;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Query\SelectLastConnectionBusinessErrorsQueryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetConnectionBusinessErrorsHandlerTest extends TestCase
{
    private SelectLastConnectionBusinessErrorsQueryInterface|MockObject $selectLastConnectionBusinessErrorsQuery;
    private GetConnectionBusinessErrorsHandler $sut;

    protected function setUp(): void
    {
        $this->selectLastConnectionBusinessErrorsQuery = $this->createMock(SelectLastConnectionBusinessErrorsQueryInterface::class);
        $this->sut = new GetConnectionBusinessErrorsHandler($this->selectLastConnectionBusinessErrorsQuery);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(GetConnectionBusinessErrorsHandler::class, $this->sut);
    }

    public function test_it_returns_the_connection_business_errors(): void
    {
        $this->selectLastConnectionBusinessErrorsQuery->method('execute')->with('erp', '2020-01-01')->willReturn(['business_errors']);
        $query = new GetConnectionBusinessErrorsQuery('erp', '2020-01-01');
        $this->assertSame(['business_errors'], $this->sut->handle($query));
    }
}
