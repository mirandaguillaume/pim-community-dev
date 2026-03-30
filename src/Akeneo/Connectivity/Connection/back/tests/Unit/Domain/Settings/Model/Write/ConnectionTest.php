<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\Settings\Model\Write;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ClientId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionImage;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionLabel;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
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
            42,
            24,
            null,
            true,
            'connection_type'
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(Connection::class, $this->sut);
    }

    public function test_it_returns_the_code(): void
    {
        $this->assertEquals(new ConnectionCode('magento'), $this->sut->code());
    }

    public function test_it_returns_the_label(): void
    {
        $this->assertEquals(new ConnectionLabel('Magento Connector'), $this->sut->label());
    }

    public function test_it_returns_the_flow_type(): void
    {
        $this->assertEquals(new FlowType(FlowType::DATA_DESTINATION), $this->sut->flowType());
    }

    public function test_it_returns_the_client_id(): void
    {
        $this->assertEquals(new ClientId(42), $this->sut->clientId());
    }

    public function test_it_returns_the_user_id(): void
    {
        $this->assertEquals(new UserId(24), $this->sut->userId());
    }

    public function test_it_returns_the_auditable(): void
    {
        $this->sut->auditable()->shouldBeBoolean();
        $this->assertSame(true, $this->sut->auditable());
    }

    public function test_it_provides_the_image(): void
    {
        $this->sut = new Connection(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            42,
            24,
            'a/b/c/image_path.jpg',
            false
        );
        $this->assertEquals(new ConnectionImage('a/b/c/image_path.jpg'), $this->sut->image());
    }

    public function test_it_is_instantiable_without_image(): void
    {
        $this->assertNull($this->sut->image());
    }

    public function test_it_changes_the_image(): void
    {
        $this->assertNull($this->sut->image());
        $image = new ConnectionImage('a/b/c/image_path.jpg');
        $this->sut->setImage($image);
        $this->assertSame($image, $this->sut->image());
    }

    public function test_it_changes_the_label(): void
    {
        $this->assertEquals(new ConnectionLabel('Magento Connector'), $this->sut->label());
        $this->sut->setLabel(new ConnectionLabel('Bynder'));
        $this->assertEquals(new ConnectionLabel('Bynder'), $this->sut->label());
    }

    public function test_it_changes_the_flow_type(): void
    {
        $this->assertEquals(new FlowType(FlowType::DATA_DESTINATION), $this->sut->flowType());
        $this->sut->setFlowType(new FlowType(FlowType::OTHER));
        $this->assertEquals(new FlowType(FlowType::OTHER), $this->sut->flowType());
    }

    public function test_it_changes_the_auditable(): void
    {
        $this->assertSame(true, $this->sut->auditable());
        $this->sut->disableAudit();
        $this->assertSame(false, $this->sut->auditable());
    }

    public function test_it_returns_the_type(): void
    {
        $this->assertEquals(new ConnectionType('connection_type'), $this->sut->type());
    }

    public function test_it_returns_the_default_type_when_type_is_omitted(): void
    {
        $this->sut = new Connection(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            42,
            24,
            null,
            true
        );
        $this->assertEquals(new ConnectionType('default'), $this->sut->type());
    }
}
