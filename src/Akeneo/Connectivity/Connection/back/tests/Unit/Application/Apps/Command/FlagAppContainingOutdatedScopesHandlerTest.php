<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\FlagAppContainingOutdatedScopesCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\FlagAppContainingOutdatedScopesHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Notifier\AuthorizationRequestNotifierInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Security\ScopeMapperRegistryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\SaveConnectedAppOutdatedScopesFlagQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\ScopeListComparator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlagAppContainingOutdatedScopesHandlerTest extends TestCase
{
    private ScopeMapperRegistryInterface|MockObject $scopeMapperRegistry;
    private SaveConnectedAppOutdatedScopesFlagQueryInterface|MockObject $saveConnectedAppOutdatedScopesFlagQuery;
    private AuthorizationRequestNotifierInterface|MockObject $authorizationRequestNotifier;
    private FlagAppContainingOutdatedScopesHandler $sut;

    protected function setUp(): void
    {
        $this->scopeMapperRegistry = $this->createMock(ScopeMapperRegistryInterface::class);
        $this->saveConnectedAppOutdatedScopesFlagQuery = $this->createMock(SaveConnectedAppOutdatedScopesFlagQueryInterface::class);
        $this->authorizationRequestNotifier = $this->createMock(AuthorizationRequestNotifierInterface::class);
        $this->sut = new FlagAppContainingOutdatedScopesHandler(
            $this->scopeMapperRegistry,
            $this->saveConnectedAppOutdatedScopesFlagQuery,
            $this->authorizationRequestNotifier,
            new ScopeListComparator($this->scopeMapperRegistry),
        );
        $this->scopeMapperRegistry->method('getAllScopes')->willReturn([
        'read_scope_a',
        'write_scope_a',
        'read_scope_b',
        'write_scope_b',
        'read_scope_c',
        'read_scope_d',
        ]);
        $this->scopeMapperRegistry->method('getExhaustiveScopes')->with(['read_scope_a', 'write_scope_b'])->willReturn([
        'read_scope_a',
        'read_scope_b',
        'write_scope_b',
        ]);
        $this->scopeMapperRegistry->method('getExhaustiveScopes')->with(['read_scope_d'])->willReturn(['read_scope_d']);
        $this->scopeMapperRegistry->method('getExhaustiveScopes')->with(['read_scope_b', 'read_scope_d'])->willReturn([
        'read_scope_b',
        'read_scope_d',
        ]);
    }

    public function test_it_is_a_flag_app_containing_outdated_scopes_handler(): void
    {
        $this->assertInstanceOf(FlagAppContainingOutdatedScopesHandler::class, $this->sut);
    }

    public function test_it_flags_the_connected_app_on_new_scopes(): void
    {
        $connectedApp = new ConnectedApp(
            'a_connected_app_id',
            'a_connected_app_name',
            ['read_scope_d', 'read_scope_b'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group',
            'an_username',
        );
        $this->sut->handle(new FlagAppContainingOutdatedScopesCommand(
            $connectedApp,
            'read_scope_a openid_scope_a random noise write_scope_b'
        ));
        $this->saveConnectedAppOutdatedScopesFlagQuery->method('execute')->with('a_connected_app_id', true);
        $this->authorizationRequestNotifier->method('notify')->with($connectedApp);
    }

    public function test_it_does_not_flag_the_connected_app_on_less_scopes(): void
    {
        $connectedApp = new ConnectedApp(
            'a_connected_app_id',
            'a_connected_app_name',
            ['read_scope_d', 'read_scope_b'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group',
            'an_username',
        );
        $this->sut->handle(new FlagAppContainingOutdatedScopesCommand(
            $connectedApp,
            'openid_scope_a random noise read_scope_d'
        ));
        $this->saveConnectedAppOutdatedScopesFlagQuery->method('execute')->with('a_connected_app_id', true);
        $this->authorizationRequestNotifier->method('notify')->with($connectedApp);
    }

    public function test_it_does_not_flag_the_connected_app_on_same_scopes(): void
    {
        $connectedApp = new ConnectedApp(
            'a_connected_app_id',
            'a_connected_app_name',
            ['read_scope_d', 'read_scope_b'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group',
            'an_username',
        );
        $this->sut->handle(new FlagAppContainingOutdatedScopesCommand(
            $connectedApp,
            'read_scope_b openid_scope_a random noise read_scope_d'
        ));
        $this->saveConnectedAppOutdatedScopesFlagQuery->method('execute')->with('a_connected_app_id', true);
        $this->authorizationRequestNotifier->method('notify')->with($connectedApp);
    }
}
