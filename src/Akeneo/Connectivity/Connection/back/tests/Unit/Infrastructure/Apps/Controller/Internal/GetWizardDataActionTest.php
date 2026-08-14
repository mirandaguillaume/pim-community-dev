<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\ScopeListComparatorInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByIdQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetUserConsentedAuthenticationScopesQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\HasUserConsentForAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal\GetWizardDataAction;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ConnectedPimUserProvider;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetWizardDataActionTest extends TestCase
{
    private const string APP_ID = 'app_prototype_id';
    private const string CLIENT_ID = 'app_prototype_client_id';
    private const int USER_ID = 42;

    private const array READ_PRODUCTS_MESSAGE = ['icon' => 'products', 'type' => 'view', 'entities' => 'products'];
    private const array DELETE_PRODUCTS_MESSAGE = ['icon' => 'products', 'type' => 'delete', 'entities' => 'products'];

    private GetAppQueryInterface|MockObject $getAppQuery;
    private AppAuthorizationSessionInterface|MockObject $appAuthorizationSession;
    private FindOneConnectedAppByIdQueryInterface|MockObject $findOneConnectedAppByIdQuery;
    private ScopeListComparatorInterface|MockObject $scopeListComparator;
    private ConnectedPimUserProvider|MockObject $connectedPimUserProvider;
    private GetUserConsentedAuthenticationScopesQueryInterface|MockObject $getUserConsentedAuthenticationScopesQuery;
    private HasUserConsentForAppQueryInterface|MockObject $hasUserConsentForAppQuery;
    private GetWizardDataAction $sut;

    protected function setUp(): void
    {
        $this->getAppQuery = $this->createMock(GetAppQueryInterface::class);
        $this->appAuthorizationSession = $this->createMock(AppAuthorizationSessionInterface::class);
        $this->findOneConnectedAppByIdQuery = $this->createMock(FindOneConnectedAppByIdQueryInterface::class);
        $this->scopeListComparator = $this->createMock(ScopeListComparatorInterface::class);
        $this->connectedPimUserProvider = $this->createMock(ConnectedPimUserProvider::class);
        $this->getUserConsentedAuthenticationScopesQuery = $this->createMock(GetUserConsentedAuthenticationScopesQueryInterface::class);
        $this->hasUserConsentForAppQuery = $this->createMock(HasUserConsentForAppQueryInterface::class);

        $this->sut = new GetWizardDataAction(
            $this->getAppQuery,
            $this->appAuthorizationSession,
            // ScopeMapperRegistry is final: a real instance is built from a mocked ScopeMapperInterface.
            new ScopeMapperRegistry([$this->createProductScopeMapper()]),
            $this->findOneConnectedAppByIdQuery,
            $this->scopeListComparator,
            $this->connectedPimUserProvider,
            $this->getUserConsentedAuthenticationScopesQuery,
            $this->hasUserConsentForAppQuery,
        );
    }

    public function test_it_redirects_on_missing_xmlhttprequest_header(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('isXmlHttpRequest')->willReturn(false);

        $this->getAppQuery->expects($this->never())->method('execute')->with($this->anything());

        $this->assertEquals(new RedirectResponse('/'), $this->sut->__invoke($request, self::CLIENT_ID));
    }

    public function test_it_throws_a_not_found_exception_when_the_app_does_not_exist(): void
    {
        $this->getAppQuery->method('execute')->with(self::CLIENT_ID)->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Invalid app identifier');
        $this->sut->__invoke($this->createXmlHttpRequest(), self::CLIENT_ID);
    }

    public function test_it_throws_a_not_found_exception_when_the_authorization_is_missing_from_the_session(): void
    {
        $this->getAppQuery->method('execute')->with(self::CLIENT_ID)->willReturn($this->createApp());
        $this->appAuthorizationSession->method('getAppAuthorization')->with(self::CLIENT_ID)->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Invalid app identifier');
        $this->sut->__invoke($this->createXmlHttpRequest(), self::CLIENT_ID);
    }

    public function test_it_returns_the_wizard_data_of_a_first_app_connection(): void
    {
        $this->getAppQuery->method('execute')->with(self::CLIENT_ID)->willReturn($this->createApp());
        $this->appAuthorizationSession->method('getAppAuthorization')->with(self::CLIENT_ID)->willReturn(
            $this->createAppAuthorization(
                ['read_products', 'write_products', 'delete_products'],
                ['openid', 'profile', 'email'],
            )
        );

        // No connected app yet: this is what makes the consent checkbox appear.
        $this->findOneConnectedAppByIdQuery->method('execute')->with(self::APP_ID)->willReturn(null);
        $this->scopeListComparator
            ->expects($this->once())
            ->method('diff')
            ->with(['delete_products', 'read_products', 'write_products'], [])
            ->willReturn(['delete_products', 'read_products', 'write_products']);

        $this->connectedPimUserProvider->method('getCurrentUserId')->willReturn(self::USER_ID);
        $this->hasUserConsentForAppQuery->method('execute')->with(self::USER_ID, self::APP_ID)->willReturn(false);
        $this->getUserConsentedAuthenticationScopesQuery
            ->expects($this->never())
            ->method('execute')
            ->with($this->anything(), $this->anything());

        $payload = $this->invokeAndDecode();

        $this->assertEquals([
            'appName' => 'App prototype',
            'appLogo' => 'https://marketplace.test/app-prototype/logo.png',
            'appUrl' => 'https://marketplace.test/app-prototype',
            'appIsCertified' => true,
            'oldScopeMessages' => null,
            'scopeMessages' => [self::DELETE_PRODUCTS_MESSAGE],
            'oldAuthenticationScopes' => null,
            'authenticationScopes' => ['email', 'profile'],
            'displayCheckboxConsent' => true,
        ], $payload);

        // Strict re-assertions on the three observables the acceptance scenario depends on.
        $this->assertTrue($payload['displayCheckboxConsent']);
        $this->assertSame(['email', 'profile'], $payload['authenticationScopes']);
        $this->assertSame([self::DELETE_PRODUCTS_MESSAGE], $payload['scopeMessages']);
    }

    public function test_it_hides_the_consent_checkbox_and_returns_the_old_scopes_when_the_app_is_already_connected(): void
    {
        $this->getAppQuery->method('execute')->with(self::CLIENT_ID)->willReturn($this->createApp());
        $this->appAuthorizationSession->method('getAppAuthorization')->with(self::CLIENT_ID)->willReturn(
            $this->createAppAuthorization(
                ['read_products', 'write_products', 'delete_products'],
                ['openid', 'profile', 'email'],
            )
        );

        $this->findOneConnectedAppByIdQuery->method('execute')->with(self::APP_ID)->willReturn(
            $this->createConnectedApp(['read_products'])
        );
        $this->scopeListComparator
            ->expects($this->once())
            ->method('diff')
            ->with(['delete_products', 'read_products', 'write_products'], ['read_products'])
            ->willReturn(['delete_products', 'write_products']);

        $this->connectedPimUserProvider->method('getCurrentUserId')->willReturn(self::USER_ID);
        $this->hasUserConsentForAppQuery->method('execute')->with(self::USER_ID, self::APP_ID)->willReturn(false);

        $payload = $this->invokeAndDecode();

        $this->assertFalse($payload['displayCheckboxConsent']);
        $this->assertSame([self::READ_PRODUCTS_MESSAGE], $payload['oldScopeMessages']);
        $this->assertSame([self::DELETE_PRODUCTS_MESSAGE], $payload['scopeMessages']);
    }

    public function test_it_only_returns_the_authentication_scopes_the_user_has_not_consented_to_yet(): void
    {
        $this->getAppQuery->method('execute')->with(self::CLIENT_ID)->willReturn($this->createApp());
        $this->appAuthorizationSession->method('getAppAuthorization')->with(self::CLIENT_ID)->willReturn(
            $this->createAppAuthorization(['read_products'], ['openid', 'profile', 'email'])
        );

        $this->findOneConnectedAppByIdQuery->method('execute')->with(self::APP_ID)->willReturn(null);
        $this->scopeListComparator->method('diff')->willReturn(['read_products']);

        $this->connectedPimUserProvider->method('getCurrentUserId')->willReturn(self::USER_ID);
        $this->hasUserConsentForAppQuery->method('execute')->with(self::USER_ID, self::APP_ID)->willReturn(true);
        $this->getUserConsentedAuthenticationScopesQuery
            ->expects($this->once())
            ->method('execute')
            ->with(self::USER_ID, self::APP_ID)
            ->willReturn(['openid', 'email']);

        $payload = $this->invokeAndDecode();

        $this->assertSame(['email'], $payload['oldAuthenticationScopes']);
        $this->assertSame(['profile'], $payload['authenticationScopes']);
    }

    public function test_it_never_asks_for_consent_on_the_openid_scope(): void
    {
        $this->getAppQuery->method('execute')->with(self::CLIENT_ID)->willReturn($this->createApp());
        $this->appAuthorizationSession->method('getAppAuthorization')->with(self::CLIENT_ID)->willReturn(
            $this->createAppAuthorization(['read_products'], ['openid'])
        );

        $this->findOneConnectedAppByIdQuery->method('execute')->with(self::APP_ID)->willReturn(null);
        $this->scopeListComparator->method('diff')->willReturn(['read_products']);

        $this->connectedPimUserProvider->method('getCurrentUserId')->willReturn(self::USER_ID);
        $this->hasUserConsentForAppQuery->method('execute')->with(self::USER_ID, self::APP_ID)->willReturn(false);

        $payload = $this->invokeAndDecode();

        $this->assertSame([], $payload['authenticationScopes']);
    }

    /**
     * @return array<string, mixed>
     */
    private function invokeAndDecode(): array
    {
        $response = $this->sut->__invoke($this->createXmlHttpRequest(), self::CLIENT_ID);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        return \json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }

    private function createXmlHttpRequest(): Request|MockObject
    {
        $request = $this->createMock(Request::class);
        $request->method('isXmlHttpRequest')->willReturn(true);

        return $request;
    }

    private function createApp(): App
    {
        return App::fromWebMarketplaceValues([
            'id' => self::APP_ID,
            'name' => 'App prototype',
            'logo' => 'https://marketplace.test/app-prototype/logo.png',
            'author' => 'Akeneo',
            'url' => 'https://marketplace.test/app-prototype',
            'categories' => ['E-commerce'],
            'certified' => true,
            'activate_url' => 'https://app-prototype.test/activate',
            'callback_url' => 'https://app-prototype.test/callback',
        ]);
    }

    /**
     * @param array<string> $authorizationScopes
     * @param array<string> $authenticationScopes
     */
    private function createAppAuthorization(array $authorizationScopes, array $authenticationScopes): AppAuthorization
    {
        return AppAuthorization::createFromRequest(
            self::CLIENT_ID,
            ScopeList::fromScopes($authorizationScopes),
            ScopeList::fromScopes($authenticationScopes),
            'https://app-prototype.test/callback',
        );
    }

    /**
     * @param array<string> $scopes
     */
    private function createConnectedApp(array $scopes): ConnectedApp
    {
        return new ConnectedApp(
            self::APP_ID,
            'App prototype',
            $scopes,
            'app_prototype_connection_code',
            'https://marketplace.test/app-prototype/logo.png',
            'Akeneo',
            'app_prototype_group',
            'app_prototype_username',
        );
    }

    /**
     * Behaves like the real product scope mapper: delete_products sits above write_products,
     * which sits above read_products.
     */
    private function createProductScopeMapper(): ScopeMapperInterface|MockObject
    {
        $scopeMapper = $this->createMock(ScopeMapperInterface::class);
        $scopeMapper->method('getScopes')->willReturn(['read_products', 'write_products', 'delete_products']);
        $scopeMapper->method('getLowerHierarchyScopes')->willReturnCallback(
            static fn (string $scope): array => match ($scope) {
                'write_products' => ['read_products'],
                'delete_products' => ['read_products', 'write_products'],
                default => [],
            }
        );
        $scopeMapper->method('getMessage')->willReturnCallback(
            static fn (string $scope): ?array => match ($scope) {
                'read_products' => self::READ_PRODUCTS_MESSAGE,
                'write_products' => ['icon' => 'products', 'type' => 'edit', 'entities' => 'products'],
                'delete_products' => self::DELETE_PRODUCTS_MESSAGE,
                default => null,
            }
        );

        return $scopeMapper;
    }
}
