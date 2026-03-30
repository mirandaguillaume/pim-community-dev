<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\ServiceApi\Service;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;
use Akeneo\Connectivity\Connection\ServiceApi\Service\ConnectedAppRemover;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectedAppRemoverTest extends TestCase
{
    private ConnectedAppRemover $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_initializable(): void
    {
        $deleteAppHandler = $this->createMock(DeleteAppHandler::class);

        $this->sut = new ConnectedAppRemover($deleteAppHandler);
        $this->assertTrue(is_a(ConnectedAppRemover::class, ConnectedAppRemover::class, true));
    }

    public function test_it_deletes_a_connected_app(): void
    {
        $deleteAppHandler = $this->createMock(DeleteAppHandler::class);

        $this->sut = new ConnectedAppRemover($deleteAppHandler);
        $deleteAppHandler->expects($this->once())->method('handle')->with($this->isInstanceOf(DeleteAppCommand::class));
        $this->sut->remove('fake_id');
    }
}
