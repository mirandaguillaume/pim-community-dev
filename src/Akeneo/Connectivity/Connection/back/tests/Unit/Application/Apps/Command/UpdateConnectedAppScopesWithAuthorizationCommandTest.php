<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationCommand;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateConnectedAppScopesWithAuthorizationCommandTest extends TestCase
{
    private UpdateConnectedAppScopesWithAuthorizationCommand $sut;

    protected function setUp(): void
    {
        $this->sut = new UpdateConnectedAppScopesWithAuthorizationCommand('test');
    }

    public function test_it_is_instantiable(): void
    {
        $this->assertInstanceOf(UpdateConnectedAppScopesWithAuthorizationCommand::class, $this->sut);
    }

    public function test_it_gets_client_id(): void
    {
        $this->assertSame('test', $this->sut->getClientId());
    }
}
