<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Settings\Command;

use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionPasswordCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionPasswordHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Service\RegenerateUserPasswordInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Pierre Jolly <pierre/jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegenerateConnectionPasswordHandlerTest extends TestCase
{
    private ConnectionRepositoryInterface|MockObject $repository;
    private RegenerateUserPasswordInterface|MockObject $regenerateUserPassword;
    private RegenerateConnectionPasswordHandler $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ConnectionRepositoryInterface::class);
        $this->regenerateUserPassword = $this->createMock(RegenerateUserPasswordInterface::class);
        $this->sut = new RegenerateConnectionPasswordHandler($this->repository, $this->regenerateUserPassword);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(RegenerateConnectionPasswordHandler::class, $this->sut);
    }

    public function test_it_regenerates_a_user_password(): void
    {
        $userId = new UserId(72);
        $connection = new Connection(
            'magento',
            'Magento Connector',
            FlowType::DATA_DESTINATION,
            42,
            $userId->id(),
            null,
            false,
        );
        $this->repository->method('findOneByCode')->with('magento')->willReturn($connection);
        $this->regenerateUserPassword->expects($this->once())->method('execute')->with($userId);
        $command = new RegenerateConnectionPasswordCommand('magento');
        $this->sut->handle($command);
    }

    public function test_it_throws_an_exception_when_the_connection_does_not_exist(): void
    {
        $this->repository->method('findOneByCode')->with('magento')->willReturn(null);
        $this->regenerateUserPassword->expects($this->never())->method('execute')->with($this->anything());
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->handle(new RegenerateConnectionPasswordCommand('magento'));
    }
}
