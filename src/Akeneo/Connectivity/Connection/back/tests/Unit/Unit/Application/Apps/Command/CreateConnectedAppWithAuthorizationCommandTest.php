<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateConnectedAppWithAuthorizationCommand;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateConnectedAppWithAuthorizationCommandTest extends TestCase
{
    private CreateConnectedAppWithAuthorizationCommand $sut;

    protected function setUp(): void
    {
        $this->sut = new CreateConnectedAppWithAuthorizationCommand('test');
    }

    public function test_it_is_instantiable(): void
    {
        $this->assertInstanceOf(CreateConnectedAppWithAuthorizationCommand::class, $this->sut);
    }

    public function test_it_gets_client_id(): void
    {
        $this->assertSame('test', $this->sut->getClientId());
    }
}
