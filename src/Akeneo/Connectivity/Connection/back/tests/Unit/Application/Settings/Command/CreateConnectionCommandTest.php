<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use PHPUnit\Framework\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateConnectionCommandTest extends TestCase
{
    private CreateConnectionCommand $sut;

    protected function setUp(): void
    {
        $this->sut = new CreateConnectionCommand('Magento', 'Magento Connector', FlowType::DATA_DESTINATION, true, 'connection_type');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CreateConnectionCommand::class, $this->sut);
    }

    public function test_it_returns_the_code(): void
    {
        $this->assertSame('Magento', $this->sut->code());
    }

    public function test_it_returns_the_label(): void
    {
        $this->assertSame('Magento Connector', $this->sut->label());
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
        $this->assertSame('connection_type', $this->sut->type());
    }
}
