<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Controller\Public;

use Akeneo\Connectivity\Connection\Application\Apps\Command\FlagAppContainingOutdatedScopesCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\FlagAppContainingOutdatedScopesHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByUserIdentifierQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public\FlagConnectedAppWithOutdatedScopesAction;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

class FlagConnectedAppWithOutdatedScopesActionTest extends TestCase
{
    private TokenStorageInterface|MockObject $tokenStorage;
    private FindOneConnectedAppByUserIdentifierQueryInterface|MockObject $findOneConnectedAppByUserIdentifierQuery;
    private FlagAppContainingOutdatedScopesHandler|MockObject $flagAppContainingOutdatedScopesHandler;
    private FlagConnectedAppWithOutdatedScopesAction $sut;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->findOneConnectedAppByUserIdentifierQuery = $this->createMock(FindOneConnectedAppByUserIdentifierQueryInterface::class);
        $this->flagAppContainingOutdatedScopesHandler = $this->createMock(FlagAppContainingOutdatedScopesHandler::class);
        $this->sut = new FlagConnectedAppWithOutdatedScopesAction(
            $this->tokenStorage,
            $this->findOneConnectedAppByUserIdentifierQuery,
            $this->flagAppContainingOutdatedScopesHandler,
        );
    }

    public function test_it_throws_access_denied_http_exception_when_no_token_was_found(): void
    {
        $request = $this->createMock(Request::class);

        $this->tokenStorage->method('getToken')->willReturn(null);
        $this->expectException(AccessDeniedHttpException::class);

        $this->expectExceptionMessage('Not an authenticated App');
        $this->sut->__invoke($request);
    }

    public function test_it_throws_access_denied_http_exception_when_no_user_was_found(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $request = $this->createMock(Request::class);

        $token->method('getUser')->willReturn(null);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $this->expectException(AccessDeniedHttpException::class);

        $this->expectExceptionMessage('Not an authenticated App');
        $this->sut->__invoke($request);
    }

    public function test_it_throws_logic_exception_when_user_returned_do_not_implement_user_interface(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $nonAkeneoUser = $this->createMock(UserInterface::class);
        $request = $this->createMock(Request::class);

        $token->method('getUser')->willReturn($nonAkeneoUser);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $this->expectException(\LogicException::class);
        $this->sut->__invoke($request);
    }

    public function test_it_throws_access_denied_http_exception_when_no_connected_app_was_found(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);
        $request = $this->createMock(Request::class);

        $user->method('getUserIdentifier')->willReturn('userIdentifier');
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $this->findOneConnectedAppByUserIdentifierQuery->method('execute')->with('userIdentifier')->willReturn(null);
        $this->expectException(AccessDeniedHttpException::class);

        $this->expectExceptionMessage('Not an authenticated App');
        $this->sut->__invoke($request);
    }

    public function test_it_flags_app_containing_outdated_scopes(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);
        $request = $this->createMock(Request::class);

        $user->method('getUserIdentifier')->willReturn('userIdentifier');
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $connectedApp = new ConnectedApp(
            'id',
            'name',
            ['some', 'scopes'],
            'connection_code',
            'path/to/logo',
            'author',
            'user_group_name',
            'an_username',
        );
        $this->findOneConnectedAppByUserIdentifierQuery->method('execute')->with('userIdentifier')->willReturn($connectedApp);
        $request->query = new InputBag(['scopes' => 'some other scopes']);
        $this->flagAppContainingOutdatedScopesHandler->expects($this->once())->method('handle')->with(new FlagAppContainingOutdatedScopesCommand(
            $connectedApp,
            'some other scopes',
        ));
        $this->assertEquals(new JsonResponse('Ok'), $this->sut->__invoke($request));
    }

    public function test_it_uses_empty_string_when_scopes_are_not_provided(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $user = $this->createMock(UserInterface::class);
        $request = $this->createMock(Request::class);

        $user->method('getUserIdentifier')->willReturn('userIdentifier');
        $token->method('getUser')->willReturn($user);
        $this->tokenStorage->method('getToken')->willReturn($token);
        $connectedApp = new ConnectedApp(
            'id',
            'name',
            ['some', 'scopes'],
            'connection_code',
            'path/to/logo',
            'author',
            'user_group_name',
            'an_username',
        );
        $this->findOneConnectedAppByUserIdentifierQuery->method('execute')->with('userIdentifier')->willReturn($connectedApp);
        $request->query = new InputBag();
        $this->flagAppContainingOutdatedScopesHandler->expects($this->once())->method('handle')->with(new FlagAppContainingOutdatedScopesCommand(
            $connectedApp,
            '',
        ));
        $this->assertEquals(new JsonResponse('Ok'), $this->sut->__invoke($request));
    }
}
