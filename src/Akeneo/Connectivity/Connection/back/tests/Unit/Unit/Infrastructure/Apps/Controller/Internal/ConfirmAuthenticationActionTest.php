<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthenticationException;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal\ConfirmAuthenticationAction;
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
class ConfirmAuthenticationActionTest extends TestCase
{
    private FeatureFlag|MockObject $marketplaceActivateFeatureFlag;
    private GetAppConfirmationQueryInterface|MockObject $getAppConfirmationQuery;
    private SecurityFacade|MockObject $security;
    private RedirectUriWithAuthorizationCodeGeneratorInterface|MockObject $redirectUriWithAuthorizationCodeGenerator;
    private AppAuthorizationSessionInterface|MockObject $appAuthorizationSession;
    private ConnectedPimUserProvider|MockObject $connectedPimUserProvider;
    private ConsentAppAuthenticationHandler|MockObject $consentAppAuthenticationHandler;
    private LoggerInterface|MockObject $logger;
    private ViolationListNormalizer|MockObject $violationListNormalizer;
    private GetAppQueryInterface|MockObject $getAppQuery;
    private ConfirmAuthenticationAction $sut;

    protected function setUp(): void
    {
        $this->marketplaceActivateFeatureFlag = $this->createMock(FeatureFlag::class);
        $this->getAppConfirmationQuery = $this->createMock(GetAppConfirmationQueryInterface::class);
        $this->security = $this->createMock(SecurityFacade::class);
        $this->redirectUriWithAuthorizationCodeGenerator = $this->createMock(RedirectUriWithAuthorizationCodeGeneratorInterface::class);
        $this->appAuthorizationSession = $this->createMock(AppAuthorizationSessionInterface::class);
        $this->connectedPimUserProvider = $this->createMock(ConnectedPimUserProvider::class);
        $this->consentAppAuthenticationHandler = $this->createMock(ConsentAppAuthenticationHandler::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->violationListNormalizer = $this->createMock(ViolationListNormalizer::class);
        $this->getAppQuery = $this->createMock(GetAppQueryInterface::class);
        $this->sut = new ConfirmAuthenticationAction(
            $this->marketplaceActivateFeatureFlag,
            $this->getAppConfirmationQuery,
            $this->security,
            $this->redirectUriWithAuthorizationCodeGenerator,
            $this->appAuthorizationSession,
            $this->connectedPimUserProvider,
            $this->consentAppAuthenticationHandler,
            $this->logger,
            $this->violationListNormalizer,
            $this->getAppQuery,
        );
    }

    public function test_it_is_confirm_authentication_action(): void
    {
        $this->sut->beAnInstanceOf(ConfirmAuthenticationAction::class);
    }

    public function test_it_throws_not_found_exception_with_feature_flag_disabled(): void
    {
        $request = $this->createMock(Request::class);

        $clientId = 'a_client_id';
        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(false);
        $this->expectException(new NotFoundHttpException());
        $this->sut->__invoke($request, $clientId);
    }

    public function test_it_redirects_if_not_xml_http_request(): void
    {
        $request = $this->createMock(Request::class);

        $clientId = 'a_client_id';
        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(false);
        $this->assertEquals(new RedirectResponse('/'), $this->sut->__invoke($request, $clientId));
    }

    public function test_it_returns_not_found_response_because_there_is_no_app_matching_the_client_id(): void
    {
        $request = $this->createMock(Request::class);

        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(true);
        $clientId = 'a_client_id';
        $this->getAppQuery->method('execute')->with($clientId)->willReturn(null);
        $result = $this->__invoke($request, $clientId);
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

    public function test_it_throws_access_denied_exception_when_the_app_is_found_but_permissions_are_missing(): void
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
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_open_apps')->willReturn(false);
        $this->expectException(AccessDeniedHttpException::class);
        $this->sut->__invoke($request, $clientId);
    }

    public function test_it_failed_because_of_consent_app_authentication_validation_error(): void
    {
        $request = $this->createMock(Request::class);

        $clientId = 'a_client_id';
        $connectedPimUserId = 1;
        $constraintViolationList = new ConstraintViolationList([
                    new ConstraintViolation('a_violated_constraint_message', '', [], '', 'a_property_path', ''),
                ]);
        $normalizedConstraintViolationList = [
                    [
                        'message' => 'a_violated_constraint_message',
                        'property_path' => 'a_property_path',
                    ],
                ];
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
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_open_apps')->willReturn(true);
        $this->connectedPimUserProvider->method('getCurrentUserId')->willReturn($connectedPimUserId);
        $this->consentAppAuthenticationHandler->method('handle')->with(new ConsentAppAuthenticationCommand($clientId, $connectedPimUserId))->willThrowException(new InvalidAppAuthenticationException($constraintViolationList));
        $this->logger->expects($this->once())->method('warning')->with('App activation failed with validation error "a_violated_constraint_message"');
        $this->violationListNormalizer->method('normalize')->with($this->anything())->willReturn($normalizedConstraintViolationList);
        $result = $this->__invoke($request, $clientId);
        Assert::assertEquals(Response::HTTP_BAD_REQUEST, $result->getStatusCode());
        Assert::assertEquals(
            \json_encode([
                        'errors' => $normalizedConstraintViolationList,
                    ]),
            $result->getContent()
        );
    }

    public function test_it_failed_because_of_consent_app_authentication_logic_exception(): void
    {
        $request = $this->createMock(Request::class);

        $clientId = 'a_client_id';
        $connectedPimUserId = 1;
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
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_open_apps')->willReturn(true);
        $this->connectedPimUserProvider->method('getCurrentUserId')->willReturn($connectedPimUserId);
        $this->consentAppAuthenticationHandler->method('handle')->with(new ConsentAppAuthenticationCommand($clientId, $connectedPimUserId))->willThrowException(new \LogicException('a_logic_exception_message'));
        $this->expectException(new \LogicException('a_logic_exception_message'));
        $this->sut->__invoke($request, $clientId);
    }

    public function test_it_throws_a_logic_exception_because_there_is_no_app_authorization_in_session(): void
    {
        $request = $this->createMock(Request::class);

        $clientId = 'a_client_id';
        $connectedPimUserId = 1;
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
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_open_apps')->willReturn(true);
        $this->connectedPimUserProvider->method('getCurrentUserId')->willReturn($connectedPimUserId);
        $this->consentAppAuthenticationHandler->expects($this->once())->method('handle')->with(new ConsentAppAuthenticationCommand($clientId, $connectedPimUserId));
        $this->appAuthorizationSession->method('getAppAuthorization')->with($clientId)->willReturn(null);
        $this->expectException(new \LogicException('There is no active app authorization in session'));
        $this->sut->__invoke($request, $clientId);
    }

    public function test_it_throws_a_logic_exception_because_there_is_no_connected_app(): void
    {
        $request = $this->createMock(Request::class);

        $clientId = 'a_client_id';
        $connectedPimUserId = 1;
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
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_open_apps')->willReturn(true);
        $this->connectedPimUserProvider->method('getCurrentUserId')->willReturn($connectedPimUserId);
        $this->consentAppAuthenticationHandler->expects($this->once())->method('handle')->with(new ConsentAppAuthenticationCommand($clientId, $connectedPimUserId));
        $this->appAuthorizationSession->method('getAppAuthorization')->with($clientId)->willReturn(AppAuthorization::createFromNormalized([
                    'client_id' => $clientId,
                    'authorization_scope' => 'write_catalog_structure delete_products read_association_types',
                    'authentication_scope' => ScopeList::fromScopes([AuthenticationScope::SCOPE_OPENID])->toScopeString(),
                    'redirect_uri' => 'a_redirect_uri',
                    'state' => 'a_state',
                ]));
        $this->getAppConfirmationQuery->method('execute')->with($clientId)->willReturn(null);
        $this->expectException(new \LogicException('The connected app should have been created'));
        $this->sut->__invoke($request, $clientId);
    }
}
