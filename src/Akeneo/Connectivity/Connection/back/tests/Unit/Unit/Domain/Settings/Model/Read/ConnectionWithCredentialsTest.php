<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Domain\Settings\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionWithCredentialsTest extends TestCase
{
    private ConnectionWithCredentials $sut;

    protected function setUp(): void
    {
        $this->sut = new ConnectionWithCredentials(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            'a/b/c/the_path.jpg',
            'my_custom_client_id',
            'my_secret',
            'my_username',
            '1',
            '2',
            true,
            'default'
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ConnectionWithCredentials::class, $this->sut);
    }

    public function test_it_returns_the_code(): void
    {
        $this->assertSame('magento', $this->sut->code());
    }

    public function test_it_returns_the_label(): void
    {
        $this->assertSame('Magento Connector', $this->sut->label());
    }

    public function test_it_returns_the_flow_type(): void
    {
        $this->assertSame(FlowType::DATA_DESTINATION, $this->sut->flowType());
    }

    public function test_it_returns_the_client_id(): void
    {
        $this->assertSame('my_custom_client_id', $this->sut->clientId());
    }

    public function test_it_returns_the_secret(): void
    {
        $this->assertSame('my_secret', $this->sut->secret());
    }

    public function test_it_returns_the_username(): void
    {
        $this->assertSame('my_username', $this->sut->username());
    }

    public function test_it_returns_null_when_the_password_is_not_set(): void
    {
        $this->assertNull($this->sut->password());
    }

    public function test_it_sets_the_password(): void
    {
        $this->assertNull($this->sut->password());
        $this->sut->setPassword('my_password');
        $this->assertSame('my_password', $this->sut->password());
    }

    public function test_it_returns_null_if_there_is_no_image(): void
    {
        $this->sut = new ConnectionWithCredentials(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            null,
            'my_custom_client_id',
            'my_secret',
            'my_username',
            '1',
            '2',
            true,
            'default'
        );
        $this->assertNull($this->sut->image());
    }

    public function test_it_returns_the_image(): void
    {
        $this->assertSame('a/b/c/the_path.jpg', $this->sut->image());
    }

    public function test_it_returns_the_user_role_id(): void
    {
        $this->assertSame('1', $this->sut->userRoleId());
    }

    public function test_it_returns_the_user_group_id(): void
    {
        $this->assertSame('2', $this->sut->userGroupId());
    }

    public function test_it_returns_the_auditable(): void
    {
        $this->assertSame(true, $this->sut->auditable());
    }

    public function test_it_returns_the_type(): void
    {
        $this->assertSame('default', $this->sut->type());
    }

    public function test_it_normalizes_a_connection_with_credentials(): void
    {
        $this->sut->setPassword('my_password');
        $this->assertSame([
                    'code' => 'magento',
                    'label' => 'Magento Connector',
                    'flow_type' => FlowType::DATA_DESTINATION,
                    'image' => 'a/b/c/the_path.jpg',
                    'client_id' => 'my_custom_client_id',
                    'secret' => 'my_secret',
                    'username' => 'my_username',
                    'password' => 'my_password',
                    'user_role_id' => '1',
                    'user_group_id' => '2',
                    'auditable' => true,
                    'type' => 'default',
                ], $this->sut->normalize());
    }
}
