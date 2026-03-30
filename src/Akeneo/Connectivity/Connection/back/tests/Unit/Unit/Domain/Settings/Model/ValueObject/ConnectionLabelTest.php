<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionLabel;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionLabelTest extends TestCase
{
    private ConnectionLabel $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_initializable(): void
    {
        $this->sut = new ConnectionLabel('Magento Connector');
        $this->assertTrue(is_a(ConnectionLabel::class, ConnectionLabel::class, true));
    }

    public function test_it_cannot_contains_a_string_shorter_than_3_characters(): void
    {
        $this->expectException(new \InvalidArgumentException('akeneo_connectivity.connection.connection.constraint.label.too_short'));
        new ConnectionLabel('aa');
    }

    public function test_it_cannot_contains_a_string_longer_than_100_characters(): void
    {
        $this->expectException(new \InvalidArgumentException('akeneo_connectivity.connection.connection.constraint.label.too_long'));
        new ConnectionLabel(\str_repeat('a', 101));
    }

    public function test_it_returns_the_connection_label_as_a_string(): void
    {
        $this->sut = new ConnectionLabel('Magento Connector');
        $this->assertSame('Magento Connector', $this->sut->__toString());
    }

    public function test_it_cannot_contains_an_empty_string(): void
    {
        $this->expectException(new \InvalidArgumentException('akeneo_connectivity.connection.connection.constraint.label.required'));
        new ConnectionLabel('');
    }
}
