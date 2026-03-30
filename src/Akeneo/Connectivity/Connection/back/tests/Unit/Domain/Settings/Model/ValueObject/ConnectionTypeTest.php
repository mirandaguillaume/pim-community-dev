<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Settings\Model\ValueObject;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionType;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectionTypeTest extends TestCase
{
    private ConnectionType $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_instantiable(): void
    {
        $this->sut = new ConnectionType('connection_type');
        $this->assertTrue(\is_a(ConnectionType::class, ConnectionType::class, true));
    }

    public function test_it_cannot_contain_an_empty_string(): void
    {
        $exceptionMessage = 'akeneo_connectivity.connection.connection.constraint.type.required';
        $this->expectException(\InvalidArgumentException::class);
        new ConnectionType('');
    }

    public function test_it_cannot_contain_a_string_longer_than_30_characters(): void
    {
        $exceptionMessage = 'akeneo_connectivity.connection.connection.constraint.type.too_long';
        $this->expectException(\InvalidArgumentException::class);
        new ConnectionType(\str_repeat('a', 31));
    }

    public function test_it_implements_to_string_and_returns_connection_type(): void
    {
        $this->sut = new ConnectionType('connection_type');
        $this->assertSame('connection_type', $this->sut->__toString());
    }

    public function test_it_returns_a_default_value(): void
    {
        $this->sut = new ConnectionType(null);
        $this->assertSame('default', $this->sut->__toString());
    }
}
