<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\Settings\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionTest extends TestCase
{
    private Connection $sut;

    protected function setUp(): void
    {
        $this->sut = new Connection(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            'a/b/c/the_path.jpg',
            true
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(Connection::class, $this->sut);
    }

    public function test_it_returns_the_code(): void
    {
        $this->assertSame('magento', $this->sut->code());
    }

    public function test_it_returns_the_label(): void
    {
        $this->assertSame('Magento Connector', $this->sut->label());
    }

    public function test_it_returns_null_if_there_is_no_image(): void
    {
        $this->sut = new Connection(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            null,
            false
        );
        $this->assertNull($this->sut->image());
    }

    public function test_it_returns_the_image(): void
    {
        $this->assertSame('a/b/c/the_path.jpg', $this->sut->image());
    }

    public function test_it_returns_the_flow_type(): void
    {
        $this->assertSame(FlowType::DATA_DESTINATION, $this->sut->flowType());
    }

    public function test_it_returns_the_auditable(): void
    {
        $this->assertSame(true, $this->sut->auditable());
    }

    public function test_it_returns_the_type(): void
    {
        $this->assertSame(ConnectionType::DEFAULT_TYPE, $this->sut->type());
    }

    public function test_it_normalizes_a_connection(): void
    {
        $this->assertSame([
                    'code' => 'magento',
                    'label' => 'Magento Connector',
                    'flowType' => FlowType::DATA_DESTINATION,
                    'image' => 'a/b/c/the_path.jpg',
                    'auditable' => true,
                    'type' => ConnectionType::DEFAULT_TYPE,
                ], $this->sut->normalize());
    }
}
