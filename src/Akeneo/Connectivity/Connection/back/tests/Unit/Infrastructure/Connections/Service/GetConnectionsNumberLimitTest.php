<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Connections\Service;

use PHPUnit\Framework\TestCase;
use spec\Akeneo\Connectivity\Connection\Infrastructure\Connections\Service\GetConnectionsNumberLimit;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetConnectionsNumberLimitTest extends TestCase
{
    private GetConnectionsNumberLimit $sut;

    protected function setUp(): void
    {
    }

    public function test_it_returns_limit_through_getter(): void
    {
        $this->sut = new GetConnectionsNumberLimit(50);
        $this->assertSame(50, $this->sut->getLimit());
    }

    public function test_it_sets_new_limit(): void
    {
        $this->sut = new GetConnectionsNumberLimit(98);
        $this->sut->setLimit(32);
        $this->assertSame(32, $this->sut->getLimit());
    }
}
