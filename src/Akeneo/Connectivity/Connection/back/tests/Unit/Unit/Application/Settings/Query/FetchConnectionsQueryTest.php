<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Settings\Query;

use Akeneo\Connectivity\Connection\Application\Settings\Query\FetchConnectionsQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionType;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FetchConnectionsQueryTest extends TestCase
{
    private FetchConnectionsQuery $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_initializable(): void
    {
        $this->sut = new FetchConnectionsQuery([]);
        $this->assertTrue(is_a(FetchConnectionsQuery::class, FetchConnectionsQuery::class, true));
    }

    public function test_it_returns_types(): void
    {
        $this->sut = new FetchConnectionsQuery([
                    'types' => [
                        ConnectionType::DEFAULT_TYPE,
                        ConnectionType::APP_TYPE,
                    ],
                ]);
        $this->assertSame([ConnectionType::DEFAULT_TYPE, ConnectionType::APP_TYPE], $this->sut->getTypes());
    }

    public function test_it_returns_an_empty_type_list(): void
    {
        $this->sut = new FetchConnectionsQuery([]);
        $this->assertSame([], $this->sut->getTypes());
    }
}
