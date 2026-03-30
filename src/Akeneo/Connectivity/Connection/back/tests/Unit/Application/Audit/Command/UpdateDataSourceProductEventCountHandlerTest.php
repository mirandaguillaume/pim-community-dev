<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Audit\Command;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataSourceProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\BulkInsertEventCountsQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\ExtractConnectionsProductEventCountQueryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateDataSourceProductEventCountHandlerTest extends TestCase
{
    private ExtractConnectionsProductEventCountQueryInterface|MockObject $extractConnectionsEventCountQuery;
    private BulkInsertEventCountsQueryInterface|MockObject $bulkInsertEventCountsQuery;
    private UpdateDataSourceProductEventCountHandler $sut;

    protected function setUp(): void
    {
        $this->extractConnectionsEventCountQuery = $this->createMock(ExtractConnectionsProductEventCountQueryInterface::class);
        $this->bulkInsertEventCountsQuery = $this->createMock(BulkInsertEventCountsQueryInterface::class);
        $this->sut = new UpdateDataSourceProductEventCountHandler($this->extractConnectionsEventCountQuery, $this->bulkInsertEventCountsQuery);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(UpdateDataSourceProductEventCountHandler::class, $this->sut);
    }
}
