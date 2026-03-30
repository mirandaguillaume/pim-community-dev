<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Settings\Model\ValueObject;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use PHPUnit\Framework\TestCase;

class ClientIdTest extends TestCase
{
    private ClientId $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_initializable(): void
    {
        $this->sut = new ClientId(42);
        $this->assertTrue(\is_a(ClientId::class, ClientId::class, true));
    }

    public function test_it_must_be_positive(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('Client id must be positive.');
        new ClientId(-1);
    }

    public function test_it_provides_the_client_id(): void
    {
        $this->sut = new ClientId(42);
        $this->assertSame(42, $this->sut->id());
    }
}
