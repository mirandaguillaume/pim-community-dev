<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Settings\Query;

use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FindAConnectionQueryTest extends TestCase
{
    private FindAConnectionQuery $sut;

    protected function setUp(): void
    {
        $this->sut = new FindAConnectionQuery('bynder');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FindAConnectionQuery::class, $this->sut);
    }

    public function test_it_returns_a_connection_code(): void
    {
        $this->assertSame('bynder', $this->sut->connectionCode());
    }
}
