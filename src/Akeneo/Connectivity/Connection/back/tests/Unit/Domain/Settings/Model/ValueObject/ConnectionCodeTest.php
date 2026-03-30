<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Settings\Model\ValueObject;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionCodeTest extends TestCase
{
    private ConnectionCode $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_initializable(): void
    {
        $this->sut = new ConnectionCode('magento');
        $this->assertTrue(\is_a(ConnectionCode::class, ConnectionCode::class, true));
    }

    public function test_it_cannot_contains_an_empty_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('akeneo_connectivity.connection.connection.constraint.code.required');
        new ConnectionCode('');
    }

    public function test_it_cannot_contains_a_string_shorter_than_3_characters(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('akeneo_connectivity.connection.connection.constraint.code.too_short');
        new ConnectionCode('aa');
    }

    public function test_it_cannot_contains_a_string_longer_than_100_characters(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('akeneo_connectivity.connection.connection.constraint.code.too_long');
        new ConnectionCode(\str_repeat('a', 103));
    }

    public function test_it_contains_only_alphanumeric_characters(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('akeneo_connectivity.connection.connection.constraint.code.invalid');
        new ConnectionCode('magento-connector');
    }

    public function test_it_returns_the_connection_code_as_a_string(): void
    {
        $this->sut = new ConnectionCode('magento');
        $this->assertSame('magento', $this->sut->__toString());
    }
}
