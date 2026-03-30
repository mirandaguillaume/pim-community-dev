<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\FlagAppContainingOutdatedScopesCommand;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlagAppContainingOutdatedScopesCommandTest extends TestCase
{
    private FlagAppContainingOutdatedScopesCommand $sut;

    protected function setUp(): void
    {
        $this->sut = new FlagAppContainingOutdatedScopesCommand($this->connectedApp, 'requested scopes');
        $this->sut->connectedApp = new ConnectedApp(
            'a_connected_app_id',
            'a_connected_app_name',
            ['a_scope'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group',
            'an_username',
            [],
            false,
            null,
            true,
        );
    }

    public function test_it_is_instantiable(): void
    {
        $this->assertInstanceOf(FlagAppContainingOutdatedScopesCommand::class, $this->sut);
    }

    public function test_it_returns_connected_app(): void
    {
        $this->assertSame($this->connectedApp, $this->sut->getConnectedApp());
    }

    public function test_it_returns_requested_scopes(): void
    {
        $this->assertSame('requested scopes', $this->sut->getRequestedScopes());
    }
}
