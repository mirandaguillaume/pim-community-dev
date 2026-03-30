<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\UpdateConnectionCommand;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateConnectionCommandTest extends TestCase
{
    private UpdateConnectionCommand $sut;

    protected function setUp(): void
    {
        $this->sut = new UpdateConnectionCommand(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            null,
            '1',
            '2',
            true
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(UpdateConnectionCommand::class, $this->sut);
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

    public function test_it_returns_null_if_there_is_no_image(): void
    {
        $this->assertNull($this->sut->image());
    }

    public function test_it_returns_the_image(): void
    {
        $this->sut = new UpdateConnectionCommand(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            'a/b/c/the_path.jpg',
            '1',
            '2',
            false
        );
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
}
