<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateConnectedAppWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateConnectedAppWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\UpdateConnectedAppScopesWithAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppConfirmation;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthenticationException;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthorizationRequestException;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByIdQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal\ConfirmAuthorizationAction;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Normalizer\ViolationListNormalizer;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\RedirectUriWithAuthorizationCodeGeneratorInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ConnectedPimUserProvider;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfirmAuthorizationActionTest extends TestCase
{
    private CreateConnectedAppWithAuthorizationHandler|MockObject $createConnectedAppWithAuthorizationHandler;
    private FeatureFlag|MockObject $marketplaceActivateFeatureFlag;
    private GetAppConfirmationQueryInterface|MockObject $getAppConfirmationQuery;
    private ViolationListNormalizer|MockObject $violationListNormalizer;
    private SecurityFacade|MockObject $security;
    private LoggerInterface|MockObject $logger;
    private RedirectUriWithAuthorizationCodeGeneratorInterface|MockObject $redirectUriWithAuthorizationCodeGenerator;
    private AppAuthorizationSessionInterface|MockObject $appAuthorizationSession;
    private ConnectedPimUserProvider|MockObject $connectedPimUserProvider;
    private ConsentAppAuthenticationHandler|MockObject $consentAppAuthenticationHandler;
    private GetAppQueryInterface|MockObject $getAppQuery;
    private FindOneConnectedAppByIdQueryInterface|MockObject $findOneConnectedAppByIdQuery;
    private UpdateConnectedAppScopesWithAuthorizationHandler|MockObject $updateConnectedAppScopesWithAuthorizationHandler;
    private ConfirmAuthorizationAction $sut;

    protected function setUp(): void
    {
        $this->createConnectedAppWithAuthorizationHandler = $this->createMock(CreateConnectedAppWithAuthorizationHandler::class);
        $this->marketplaceActivateFeatureFlag = $this->createMock(FeatureFlag::class);
        $this->getAppConfirmationQuery = $this->createMock(GetAppConfirmationQueryInterface::class);
        $this->violationListNormalizer = $this->createMock(ViolationListNormalizer::class);
        $this->security = $this->createMock(SecurityFacade::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->redirectUriWithAuthorizationCodeGenerator = $this->createMock(RedirectUriWithAuthorizationCodeGeneratorInterface::class);
        $this->appAuthorizationSession = $this->createMock(AppAuthorizationSessionInterface::class);
        $this->connectedPimUserProvider = $this->createMock(ConnectedPimUserProvider::class);
        $this->consentAppAuthenticationHandler = $this->createMock(ConsentAppAuthenticationHandler::class);
        $this->getAppQuery = $this->createMock(GetAppQueryInterface::class);
        $this->findOneConnectedAppByIdQuery = $this->createMock(FindOneConnectedAppByIdQueryInterface::class);
        $this->updateConnectedAppScopesWithAuthorizationHandler = $this->createMock(UpdateConnectedAppScopesWithAuthorizationHandler::class);
        $this->sut = new ConfirmAuthorizationAction(
            $this->createConnectedAppWithAuthorizationHandler,
            $this->marketplaceActivateFeatureFlag,
            $this->getAppConfirmationQuery,
            $this->violationListNormalizer,
            $this->security,
            $this->logger,
            $this->redirectUriWithAuthorizationCodeGenerator,
            $this->appAuthorizationSession,
            $this->connectedPimUserProvider,
            $this->consentAppAuthenticationHandler,
            $this->getAppQuery,
            $this->findOneConnectedAppByIdQuery,
            $this->updateConnectedAppScopesWithAuthorizationHandler,
        );
    }

    public function test_it_is_a_confirmation_authorization_action(): void
    {
        $this->sut->beAnInstanceOf(ConfirmAuthorizationAction::class);
    }

    public function test_it_throws_not_found_exception_with_feature_flag_disabled(): void
    {
        $request = $this->createMock(Request::class);

        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(false);
        $this->expectException(NotFoundHttpException::class);
        $this->sut->__invoke($request, 'foo');
    }

    public function test_it_redirects_on_missing_xmlhttprequest_header(): void
    {
        $request = $this->createMock(Request::class);

        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(false);
        $this->assertEquals(new RedirectResponse('/'), $this->sut->__invoke($request, 'foo'));
    }

    public function test_it_returns_not_found_response_because_there_is_no_app_matching_the_client_id(): void
    {
        $request = $this->createMock(Request::class);

        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(true);
        $clientId = 'a_client_id';
        $this->getAppQuery->method('execute')->with($clientId)->willReturn(null);
        $result = $this->sut->__invoke($request, $clientId);
        Assert::assertEquals(Response::HTTP_NOT_FOUND, $result->getStatusCode());
        Assert::assertEquals(
            \json_encode([
                        'errors' => [
                            [
                                'message' => 'akeneo_connectivity.connection.connect.apps.error.app_not_found',
                            ],
                        ],
                    ]),
            $result->getContent()
        );
    }

    public function test_it_throws_access_denied_exception_when_the_app_is_found_but_manage_apps_permission_is_missing(): void
    {
        $request = $this->createMock(Request::class);

        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(true);
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
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(false);
        $this->expectException(AccessDeniedHttpException::class);
        $this->sut->__invoke($request, $clientId);
    }

    public function test_it_throws_invalid_app_authorization_request_because_create_app_validation_failed(): void
    {
        $request = $this->createMock(Request::class);

        $connectedPimUserId = 1;
        $fosClientId = 2;
        $clientId = 'a_client_id';
        $constraintViolationList = new ConstraintViolationList([
                    new ConstraintViolation('a_violated_constraint_message', '', [], '', 'a_property_path', ''),
                ]);
        $appConfirmation = AppConfirmation::create('an_app_id', $connectedPimUserId, 'a_user_group', $fosClientId);
        $normalizedConstraintViolationList = [
                    [
                        'message' => 'a_violated_constraint_message',
                        'property_path' => 'a_property_path',
                    ],
                ];
        $appAuthorization = AppAuthorization::createFromNormalized([
                    'client_id' => $clientId,
                    'authorization_scope' => 'read_catalog_structure write_categories',
                    'authentication_scope' => ScopeList::fromScopes([AuthenticationScope::SCOPE_OPENID])->toScopeString(),
                    'redirect_uri' => 'a_redirect_uri',
                    'state' => 'a state',
                ]);
        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(true);
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
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $this->connectedPimUserProvider->method('getCurrentUserId')->willReturn($connectedPimUserId);
        $this->createConnectedAppWithAuthorizationHandler->method('handle')->with(new CreateConnectedAppWithAuthorizationCommand($clientId))->willThrowException(new InvalidAppAuthorizationRequestException($constraintViolationList));
        $this->appAuthorizationSession->method('getAppAuthorization')->with($clientId)->willReturn($appAuthorization);
        $this->getAppConfirmationQuery->method('execute')->with($clientId)->willReturn($appConfirmation);
        $this->logger->expects($this->once())->method('warning')->with($this->anything());
        $this->violationListNormalizer->method('normalize')->with($this->anything())->willReturn($normalizedConstraintViolationList);
        $result = $this->sut->__invoke($request, $clientId);
        Assert::assertEquals(Response::HTTP_BAD_REQUEST, $result->getStatusCode());
        Assert::assertEquals(
            \json_encode([
                        'errors' => $normalizedConstraintViolationList,
                    ]),
            $result->getContent()
        );
    }

    public function test_it_throws_invalid_app_authentication_exception_because_consent_app_validation_failed(): void
    {
        $request = $this->createMock(Request::class);

        $connectedPimUserId = 1;
        $fosClientId = 2;
        $clientId = 'a_client_id';
        $appConfirmation = AppConfirmation::create('an_app_id', $connectedPimUserId, 'a_user_group', $fosClientId);
        $constraintViolationList = new ConstraintViolationList([
                    new ConstraintViolation('a_violated_constraint_message', '', [], '', 'a_property_path', ''),
                ]);
        $normalizedConstraintViolationList = [
                    [
                        'message' => 'a_violated_constraint_message',
                        'property_path' => 'a_property_path',
                    ],
                ];
        $appAuthorization = AppAuthorization::createFromNormalized([
                    'client_id' => $clientId,
                    'authorization_scope' => 'read_catalog_structure write_categories',
                    'authentication_scope' => ScopeList::fromScopes([AuthenticationScope::SCOPE_OPENID])->toScopeString(),
                    'redirect_uri' => 'a_redirect_uri',
                    'state' => 'a state',
                ]);
        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(true);
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
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $this->connectedPimUserProvider->method('getCurrentUserId')->willReturn($connectedPimUserId);
        $this->createConnectedAppWithAuthorizationHandler->expects($this->once())->method('handle')->with(new CreateConnectedAppWithAuthorizationCommand($clientId));
        $this->consentAppAuthenticationHandler->method('handle')->with(new ConsentAppAuthenticationCommand($clientId, $connectedPimUserId))->willThrowException(new InvalidAppAuthenticationException($constraintViolationList));
        $this->appAuthorizationSession->method('getAppAuthorization')->with($clientId)->willReturn($appAuthorization);
        $this->getAppConfirmationQuery->method('execute')->with($clientId)->willReturn($appConfirmation);
        $this->logger->expects($this->once())->method('warning')->with($this->anything());
        $this->violationListNormalizer->method('normalize')->with($this->anything())->willReturn($normalizedConstraintViolationList);
        $result = $this->sut->__invoke($request, $clientId);
        Assert::assertEquals(Response::HTTP_BAD_REQUEST, $result->getStatusCode());
        Assert::assertEquals(
            \json_encode([
                        'errors' => $normalizedConstraintViolationList,
                    ]),
            $result->getContent()
        );
    }

    public function test_it_throws_a_logic_exception_because_there_is_no_active_app_authorization_in_session(): void
    {
        $request = $this->createMock(Request::class);

        $connectedPimUserId = 1;
        $fosClientId = 2;
        $clientId = 'a_client_id';
        $appConfirmation = AppConfirmation::create('an_app_id', $connectedPimUserId, 'a_user_group', $fosClientId);
        $constraintViolationList = new ConstraintViolationList([
                    new ConstraintViolation('a_violated_constraint_message', '', [], '', 'a_property_path', ''),
                ]);
        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(true);
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
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $this->connectedPimUserProvider->method('getCurrentUserId')->willReturn($connectedPimUserId);
        $this->createConnectedAppWithAuthorizationHandler->expects($this->once())->method('handle')->with(new CreateConnectedAppWithAuthorizationCommand($clientId));
        $this->consentAppAuthenticationHandler->method('handle')->with(new ConsentAppAuthenticationCommand($clientId, $connectedPimUserId))->willThrowException(new InvalidAppAuthenticationException($constraintViolationList));
        $this->appAuthorizationSession->method('getAppAuthorization')->with($clientId)->willReturn(null);
        $this->getAppConfirmationQuery->method('execute')->with($clientId)->willReturn($appConfirmation);
        $this->expectException(\LogicException::class);

        $this->expectExceptionMessage('There is no active app authorization in session');
        $this->sut->__invoke($request, $clientId);
    }

    public function test_it_updates_when_connected_app_already_exist(): void
    {
        $request = $this->createMock(Request::class);

        $connectedPimUserId = 1;
        $fosClientId = 2;
        $clientId = 'a_client_id';
        $appConfirmation = AppConfirmation::create('an_app_id', $connectedPimUserId, 'a_user_group', $fosClientId);
        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(true);
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
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $this->connectedPimUserProvider->method('getCurrentUserId')->willReturn($connectedPimUserId);
        $connectedApp = new ConnectedApp(
            $clientId,
            'App',
            [],
            'connectionCode_random',
            'http://www.example.com/path/to/logo',
            'author',
            'userGroup_random',
            'an_username',
            [],
            false,
            'partner'
        );
        $this->findOneConnectedAppByIdQuery->method('execute')->with($clientId)->willReturn($connectedApp);
        $this->updateConnectedAppScopesWithAuthorizationHandler->expects($this->once())->method('handle')->with(new UpdateConnectedAppScopesWithAuthorizationCommand($clientId));
        $appAuthorization = AppAuthorization::createFromNormalized([
                    'client_id' => $clientId,
                    'authorization_scope' => 'read_catalog_structure write_categories',
                    'authentication_scope' => ScopeList::fromScopes([AuthenticationScope::SCOPE_OPENID])->toScopeString(),
                    'redirect_uri' => 'a_redirect_uri',
                    'state' => 'a state',
                ]);
        $this->appAuthorizationSession->method('getAppAuthorization')->with($clientId)->willReturn($appAuthorization);
        $this->getAppConfirmationQuery->method('execute')->with($clientId)->willReturn($appConfirmation);
        $this->redirectUriWithAuthorizationCodeGenerator->method('generate')->with(
            $appAuthorization,
            $appConfirmation,
            $connectedPimUserId
        )->willReturn('http://url.test');
        $this->sut->__invoke($request, $clientId);
    }
}
