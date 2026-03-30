<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Settings\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Client;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ClientTest extends TestCase
{
    private Client $sut;

    protected function setUp(): void
    {
        $this->sut = new Client(
            42,
            'my_client_id',
            'my_secret'
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(Client::class, $this->sut);
    }

    public function test_it_returns_the_id(): void
    {
        $this->assertSame(42, $this->sut->id());
    }

    public function test_it_returns_the_client_id(): void
    {
        $this->assertSame('my_client_id', $this->sut->clientId());
    }

    public function test_it_returns_the_secret(): void
    {
        $this->assertSame('my_secret', $this->sut->secret());
    }
}
