<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Apps\ConnectedPimUserProviderInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByConnectionCodeQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetUserConsentedAuthenticationScopesQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal\GetConnectedAppAuthenticationScopesAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetConnectedAppAuthenticationScopesActionTest extends TestCase
{
    private GetUserConsentedAuthenticationScopesQueryInterface|MockObject $getUserConsentedAuthenticationScopesQuery;
    private FindOneConnectedAppByConnectionCodeQueryInterface|MockObject $findOneConnectedAppByConnectionCodeQuery;
    private ConnectedPimUserProviderInterface|MockObject $connectedPimUserProvider;
    private GetConnectedAppAuthenticationScopesAction $sut;

    protected function setUp(): void
    {
        $this->getUserConsentedAuthenticationScopesQuery = $this->createMock(GetUserConsentedAuthenticationScopesQueryInterface::class);
        $this->findOneConnectedAppByConnectionCodeQuery = $this->createMock(FindOneConnectedAppByConnectionCodeQueryInterface::class);
        $this->connectedPimUserProvider = $this->createMock(ConnectedPimUserProviderInterface::class);
        $this->sut = new GetConnectedAppAuthenticationScopesAction(
            $this->getUserConsentedAuthenticationScopesQuery,
            $this->findOneConnectedAppByConnectionCodeQuery,
            $this->connectedPimUserProvider,
        );
        $this->findOneConnectedAppByConnectionCodeQuery->method('execute')->with('app_connection_code')->willReturn(new ConnectedApp(
            'app_identifier',
            'my app',
            ['foo', 'bar'],
            'app_connection_code',
            'app_logo',
            'app_author',
            'app_123456abcdef',
            'an_username',
        ));
        $this->connectedPimUserProvider->method('getCurrentUserId')->willReturn(42);
        $this->getUserConsentedAuthenticationScopesQuery->method('execute')->with(42, 'app_identifier')->willReturn([
        'auth_scope_a',
        'auth_scope_b',
        'auth_scope_c',
        ]);
    }

    public function test_it_returns_a_list_of_authentication_scopes(): void
    {
        $this->assertEquals(new JsonResponse([
                    'auth_scope_a',
                    'auth_scope_b',
                    'auth_scope_c',
                ]), $this->sut->__invoke('app_connection_code'));
    }

    public function test_it_returns_an_empty_list_of_authentication_scopes(): void
    {
        $this->getUserConsentedAuthenticationScopesQuery->method('execute')->with(42, 'app_identifier')->willReturn([]);
        $this->assertEquals(new JsonResponse([]), $this->sut->__invoke('app_connection_code'));
    }

    public function test_it_throws_a_not_found_exception_on_non_connected_app_connection_code(): void
    {
        $this->findOneConnectedAppByConnectionCodeQuery->method('execute')->with('foo')->willReturn(null);
        $this->expectException(NotFoundHttpException::class);

        $this->expectExceptionMessage('Connected app with connection code foo does not exist.');
        $this->sut->__invoke('foo');
    }
}
