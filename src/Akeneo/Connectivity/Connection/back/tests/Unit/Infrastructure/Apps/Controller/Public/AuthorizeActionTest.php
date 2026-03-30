<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Controller\Public;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\ScopeListComparatorInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetConnectedAppScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public\AuthorizeAction;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\RedirectUriWithAuthorizationCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ConnectedPimUserProvider;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthorizeActionTest extends TestCase
{
    private RequestAppAuthorizationHandler|MockObject $requestAppAuthorizationHandler;
    private RouterInterface|MockObject $router;
    private FeatureFlag|MockObject $marketplaceActivateFeatureFlag;
    private AppAuthorizationSessionInterface|MockObject $appAuthorizationSession;
    private GetAppConfirmationQueryInterface|MockObject $getAppConfirmationQuery;
    private RedirectUriWithAuthorizationCodeGeneratorInterface|MockObject $redirectUriWithAuthorizationCodeGenerator;
    private ConnectedPimUserProvider|MockObject $connectedPimUserProvider;
    private RequestAppAuthenticationHandler|MockObject $requestAppAuthenticationHandler;
    private SecurityFacade|MockObject $security;
    private GetAppQueryInterface|MockObject $getAppQuery;
    private GetConnectedAppScopesQueryInterface|MockObject $getConnectedAppScopesQuery;
    private ScopeListComparatorInterface|MockObject $scopeListComparator;
    private UpdateConnectedAppScopesWithAuthorizationHandler|MockObject $updateConnectedAppScopesWithAuthorizationHandler;
    private ClientProviderInterface|MockObject $clientProvider;
    private AuthorizeAction $sut;

    protected function setUp(): void
    {
        $this->requestAppAuthorizationHandler = $this->createMock(RequestAppAuthorizationHandler::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->marketplaceActivateFeatureFlag = $this->createMock(FeatureFlag::class);
        $this->appAuthorizationSession = $this->createMock(AppAuthorizationSessionInterface::class);
        $this->getAppConfirmationQuery = $this->createMock(GetAppConfirmationQueryInterface::class);
        $this->redirectUriWithAuthorizationCodeGenerator = $this->createMock(RedirectUriWithAuthorizationCodeGeneratorInterface::class);
        $this->connectedPimUserProvider = $this->createMock(ConnectedPimUserProvider::class);
        $this->requestAppAuthenticationHandler = $this->createMock(RequestAppAuthenticationHandler::class);
        $this->security = $this->createMock(SecurityFacade::class);
        $this->getAppQuery = $this->createMock(GetAppQueryInterface::class);
        $this->getConnectedAppScopesQuery = $this->createMock(GetConnectedAppScopesQueryInterface::class);
        $this->scopeListComparator = $this->createMock(ScopeListComparatorInterface::class);
        $this->updateConnectedAppScopesWithAuthorizationHandler = $this->createMock(UpdateConnectedAppScopesWithAuthorizationHandler::class);
        $this->clientProvider = $this->createMock(ClientProviderInterface::class);
        $this->sut = new AuthorizeAction(
            $this->requestAppAuthorizationHandler,
            $this->router,
            $this->marketplaceActivateFeatureFlag,
            $this->appAuthorizationSession,
            $this->getAppConfirmationQuery,
            $this->redirectUriWithAuthorizationCodeGenerator,
            $this->connectedPimUserProvider,
            $this->requestAppAuthenticationHandler,
            $this->security,
            $this->getAppQuery,
            $this->getConnectedAppScopesQuery,
            $this->scopeListComparator,
            $this->updateConnectedAppScopesWithAuthorizationHandler,
            $this->clientProvider,
        );
    }

    public function test_it_is_an_authorize_action(): void
    {
        $this->sut->beAnInstanceOf(AuthorizeAction::class);
    }

    public function test_it_throws_not_found_exception_with_feature_flag_disabled(): void
    {
        $request = $this->createMock(Request::class);

        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(false);
        $this->expectException(NotFoundHttpException::class);
        $this->sut->__invoke($request);
    }

    public function test_it_redirects_because_there_is_no_client_id(): void
    {
        $request = $this->createMock(Request::class);

        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->query = new InputBag();
        $this->router->method('generate')->with('akeneo_connectivity_connection_connect_apps_authorize', [
                    'error' => 'akeneo_connectivity.connection.connect.apps.error.app_not_found',
                ])->willReturn('/connect/apps/authorize?error=akeneo_connectivity.connection.connect.apps.error.app_not_found');
        $this->assertEquals(new RedirectResponse('/#/connect/apps/authorize?error=akeneo_connectivity.connection.connect.apps.error.app_not_found'), $this->sut->__invoke($request));
    }

    public function test_it_redirects_because_there_is_no_app_matching_the_client_id(): void
    {
        $request = $this->createMock(Request::class);

        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $clientId = 'invalid_client_id';
        $request->query = new InputBag(['client_id' => $clientId]);
        $this->router->method('generate')->with('akeneo_connectivity_connection_connect_apps_authorize', [
                    'error' => 'akeneo_connectivity.connection.connect.apps.error.app_not_found',
                ])->willReturn('/connect/apps/authorize?error=akeneo_connectivity.connection.connect.apps.error.app_not_found');
        $this->getAppQuery->method('execute')->with($clientId)->willReturn(null);
        $this->assertEquals(new RedirectResponse('/#/connect/apps/authorize?error=akeneo_connectivity.connection.connect.apps.error.app_not_found'), $this->sut->__invoke($request));
    }

    public function test_it_throws_access_denied_exception_when_the_app_is_found_but_permissions_are_missing(): void
    {
        $request = $this->createMock(Request::class);

        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $clientId = 'a_client_id';
        $request->query = new InputBag(['client_id' => $clientId]);
        $app = App::fromWebMarketplaceValues([
                    'id' => $clientId,
                    'name' => 'some app',
                    'activate_url' => 'http://url.test',
                    'callback_url' => 'http://url.test',
                    'logo' => 'logo',
                    'author' => 'admin',
                    'url' => 'http://manage_app.test',
                    'categories' => ['master'],
                ]);
        $this->getAppQuery->method('execute')->with($clientId)->willReturn($app);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_open_apps')->willReturn(false);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(false);
        $this->expectException(AccessDeniedHttpException::class);
        $this->sut->__invoke($request);
    }

    public function test_it_redirects_when_there_are_new_scopes(): void
    {
        $request = $this->createMock(Request::class);

        $clientId = 'valid_client_id';
        $this->sut->setUpBeforeScopes(
            $clientId,
            $this->marketplaceActivateFeatureFlag,
            $this->router,
            $request,
            $this->getAppQuery,
            $this->security,
            $this->clientProvider,
        );
        $requestedScopes = ['write_products'];
        $originalScopes = ['read_products'];
        $diffScopes = ['write_products'];
        $appAuthorization = AppAuthorization::createFromNormalized([
                    'client_id' => $clientId,
                    'authorization_scope' => \implode(' ', $requestedScopes),
                    'authentication_scope' => '',
                    'redirect_uri' => 'http://url.test',
                    'state' => 'state',
                ]);
        $this->appAuthorizationSession->method('getAppAuthorization')->with($clientId)->willReturn($appAuthorization);
        $appConfirmation = AppConfirmation::create(
            $clientId,
            1,
            'user_group',
            1
        );
        $this->getAppConfirmationQuery->method('execute')->with($clientId)->willReturn($appConfirmation);
        $this->getConnectedAppScopesQuery->method('execute')->with($clientId)->willReturn($originalScopes);
        $this->scopeListComparator->method('diff')->with(
            $requestedScopes,
            $originalScopes
        )->willReturn($diffScopes);
        $this->assertEquals(new RedirectResponse('/#/connect/apps/authorize'), $this->sut->__invoke($request));
    }

    public function test_it_redirects_to_the_callback_url_when_there_are_unchanged_or_less_scopes(): void
    {
        $request = $this->createMock(Request::class);

        $clientId = 'valid_client_id';
        $this->sut->setUpBeforeScopes(
            $clientId,
            $this->marketplaceActivateFeatureFlag,
            $this->router,
            $request,
            $this->getAppQuery,
            $this->security,
            $this->clientProvider,
        );
        $requestedScopes = ['read_products'];
        $originalScopes = ['write_products'];
        $diffScopes = [];
        $appAuthorization = AppAuthorization::createFromNormalized([
                    'client_id' => $clientId,
                    'authorization_scope' => \implode(' ', $requestedScopes),
                    'authentication_scope' => '',
                    'redirect_uri' => 'http://url.test',
                    'state' => 'state',
                ]);
        $this->appAuthorizationSession->method('getAppAuthorization')->with($clientId)->willReturn($appAuthorization);
        $appConfirmation = AppConfirmation::create(
            $clientId,
            1,
            'user_group',
            1
        );
        $this->getAppConfirmationQuery->method('execute')->with($clientId)->willReturn($appConfirmation);
        $this->getConnectedAppScopesQuery->method('execute')->with($clientId)->willReturn($originalScopes);
        $this->scopeListComparator->method('diff')->with(
            $requestedScopes,
            $originalScopes
        )->willReturn($diffScopes);
        $currentUserId = 1;
        $this->connectedPimUserProvider->method('getCurrentUserId')->willReturn($currentUserId);
        $this->redirectUriWithAuthorizationCodeGenerator->method('generate')->with(
            $appAuthorization,
            $appConfirmation,
            $currentUserId
        )->willReturn('http://url.test');
        $this->assertEquals(new RedirectResponse('http://url.test'), $this->sut->__invoke($request));
    }

    public function test_it_only_redirects_to_the_callback_url_if_user_can_only_open_apps(): void
    {
        $request = $this->createMock(Request::class);

        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_open_apps')->willReturn(true);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(false);
        $requestedScopes = ['write_products'];
        $originalScopes = ['read_products'];
        $diffScopes = ['write_products'];
        $clientId = 'a_client_id';
        $app = App::fromWebMarketplaceValues([
                    'id' => $clientId,
                    'name' => 'some app',
                    'activate_url' => 'http://url.test',
                    'callback_url' => 'http://url.test',
                    'logo' => 'logo',
                    'author' => 'admin',
                    'url' => 'http://manage_app.test',
                    'categories' => ['master'],
                ]);
        $this->getAppQuery->method('execute')->with($clientId)->willReturn($app);
        $this->clientProvider->method('findOrCreateClient')->with($app)->willReturn(new Client());
        $appConfirmation = AppConfirmation::create(
            $clientId,
            1,
            'user_group',
            1
        );
        $this->getAppConfirmationQuery->method('execute')->with($clientId)->willReturn($appConfirmation);
        $appAuthorization = AppAuthorization::createFromNormalized([
                    'client_id' => $clientId,
                    'authorization_scope' => \implode(' ', $requestedScopes),
                    'authentication_scope' => '',
                    'redirect_uri' => 'http://url.test',
                    'state' => 'state',
                ]);
        $this->appAuthorizationSession->method('getAppAuthorization')->with($clientId)->willReturn($appAuthorization);
        $this->getConnectedAppScopesQuery->method('execute')->with($clientId)->willReturn($originalScopes);
        $this->scopeListComparator->method('diff')->with(
            $requestedScopes,
            $originalScopes
        )->willReturn($diffScopes);
        $currentUserId = 1;
        $this->connectedPimUserProvider->method('getCurrentUserId')->willReturn($currentUserId);
        $this->redirectUriWithAuthorizationCodeGenerator->method('generate')->with(
            $appAuthorization,
            $appConfirmation,
            $currentUserId
        )->willReturn('http://url.test');
        $request->query = new InputBag([
                    'client_id' => $clientId,
                    'response_type' => 'code',
                    'scope' => \implode(' ', $requestedScopes),
                    'state' => 'random_state_string',
                ]);
        $this->assertEquals(new RedirectResponse('http://url.test'), $this->sut->__invoke($request));
        $this->updateConnectedAppScopesWithAuthorizationHandler->method('handle')->with($this->isInstanceOf(UpdateConnectedAppScopesWithAuthorizationCommand::class));
    }

    public function test_it_denies_access_to_users_who_cannot_manage_and_its_first_connection_attempt(): void
    {
        $request = $this->createMock(Request::class);

        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_open_apps')->willReturn(true);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(false);
        $clientId = 'a_client_id';
        $app = App::fromWebMarketplaceValues([
                    'id' => $clientId,
                    'name' => 'some app',
                    'activate_url' => 'http://url.test',
                    'callback_url' => 'http://url.test',
                    'logo' => 'logo',
                    'author' => 'admin',
                    'url' => 'http://manage_app.test',
                    'categories' => ['master'],
                ]);
        $this->getAppQuery->method('execute')->with($clientId)->willReturn($app);
        $this->clientProvider->method('findOrCreateClient')->with($app)->willReturn(new Client());
        $this->getAppConfirmationQuery->method('execute')->with($clientId)->willReturn(null);
        $appAuthorization = AppAuthorization::createFromNormalized([
                    'client_id' => $clientId,
                    'authorization_scope' => 'write_products',
                    'authentication_scope' => '',
                    'redirect_uri' => 'http://url.test',
                    'state' => 'state',
                ]);
        $this->appAuthorizationSession->method('getAppAuthorization')->with($clientId)->willReturn($appAuthorization);
        $this->getConnectedAppScopesQuery->method('execute')->with($clientId)->willReturn([]);
        $this->scopeListComparator->method('diff')->with(
            ['write_products'],
            []
        )->willReturn([]);
        $request->query = new InputBag([
                    'client_id' => $clientId,
                    'response_type' => 'code',
                    'scope' => 'write_products',
                    'state' => 'random_state_string',
                ]);
        $this->expectException(AccessDeniedHttpException::class);
        $this->sut->__invoke($request);
        $this->updateConnectedAppScopesWithAuthorizationHandler->method('handle')->with($this->isInstanceOf(UpdateConnectedAppScopesWithAuthorizationCommand::class));
    }

    private function setUpBeforeScopes(
        string $clientId,
        FeatureFlag $marketplaceActivateFeatureFlag,
        RouterInterface $router,
        Request $request,
        GetAppQueryInterface $getAppQuery,
        SecurityFacade $security,
        ClientProviderInterface $clientProvider,
    ): void {
        $marketplaceActivateFeatureFlag->isEnabled()->willReturn(true);
    
        $request->query = new InputBag(['client_id' => $clientId]);
        $router
            ->generate('akeneo_connectivity_connection_connect_apps_authorize', [
                'client_id' => $clientId,
            ])
            ->willReturn('/connect/apps/authorize');
    
        $app = App::fromCustomAppValues([
            'id' => $clientId,
            'name' => 'custom app',
            'activate_url' => 'http://url.test',
            'callback_url' => 'http://url.test',
        ]);
    
        $getAppQuery->execute($clientId)->willReturn($app);
    
        $clientProvider->findOrCreateClient($app)->willReturn(new Client());
    
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(true);
    }
}
