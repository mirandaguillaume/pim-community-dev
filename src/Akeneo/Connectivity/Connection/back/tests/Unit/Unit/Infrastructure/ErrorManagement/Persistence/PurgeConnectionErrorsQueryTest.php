<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Persistence\PurgeConnectionErrorsQuery;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PurgeConnectionErrorsQueryTest extends TestCase
{
    private Client|MockObject $elastisearch;
    private PurgeConnectionErrorsQuery $sut;

    protected function setUp(): void
    {
        $this->elastisearch = $this->createMock(Client::class);
        $this->sut = new PurgeConnectionErrorsQuery($this->elastisearch);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(PurgeConnectionErrorsQuery::class, $this->sut);
    }

    public function test_it_does_nothing_if_there_is_no_connections_to_purge(): void
    {
        $this->elastisearch->expects($this->never())->method('msearch')->with($this->anything());
        $this->sut->execute([]);
    }
}
