<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Marketplace\AppUrlGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\IsConnectionsNumberLimitReachedQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal\GetAppActivateUrlAction;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetAppActivateUrlActionTest extends TestCase
{
    private GetAppQueryInterface|MockObject $getAppQuery;
    private SecurityFacade|MockObject $security;
    private FeatureFlag|MockObject $marketplaceActivateFeatureFlag;
    private IsConnectionsNumberLimitReachedQueryInterface|MockObject $isConnectionsNumberLimitReachedQuery;
    private GetAppActivateUrlAction $sut;

    protected function setUp(): void
    {
        $this->getAppQuery = $this->createMock(GetAppQueryInterface::class);
        $this->security = $this->createMock(SecurityFacade::class);
        $this->marketplaceActivateFeatureFlag = $this->createMock(FeatureFlag::class);
        $this->isConnectionsNumberLimitReachedQuery = $this->createMock(IsConnectionsNumberLimitReachedQueryInterface::class);
        $this->sut = new GetAppActivateUrlAction(
            $this->getAppQuery,
            new AppUrlGenerator(new PimUrl('https://some_pim_url')),
            $this->security,
            $this->marketplaceActivateFeatureFlag,
            $this->isConnectionsNumberLimitReachedQuery,
        );
    }

    public function test_it_redirects_on_missing_xmlhttprequest_header(): void
    {
        $request = $this->createMock(Request::class);

        $request->method('isXmlHttpRequest')->willReturn(false);
        $this->assertEquals(new RedirectResponse('/'), $this->sut->__invoke($request, 'foo'));
    }

    public function test_it_throws_not_found_exception_with_feature_flag_disabled(): void
    {
        $request = $this->createMock(Request::class);

        $request->method('isXmlHttpRequest')->willReturn(true);
        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(false);
        $this->expectException(new NotFoundHttpException());
        $this->sut->__invoke($request, 'foo');
    }

    public function test_it_throws_bad_request_exception_when_too_much_apps(): void
    {
        $request = $this->createMock(Request::class);

        $request->method('isXmlHttpRequest')->willReturn(true);
        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $this->isConnectionsNumberLimitReachedQuery->method('execute')->willReturn(true);
        $this->expectException(new BadRequestHttpException('App and connections limit reached'));
        $this->sut->__invoke($request, 'foo');
    }

    public function test_it_throws_not_found_exception_with_wrong_app_identifier(): void
    {
        $request = $this->createMock(Request::class);

        $request->method('isXmlHttpRequest')->willReturn(true);
        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $this->isConnectionsNumberLimitReachedQuery->method('execute')->willReturn(false);
        $this->getAppQuery->method('execute')->with('foo')->willReturn(null);
        $this->expectException(new NotFoundHttpException('Invalid app identifier'));
        $this->sut->__invoke($request, 'foo');
    }

    public function test_it_throws_access_denied_exception_when_the_app_is_found_but_manage_apps_permission_is_missing(): void
    {
        $request = $this->createMock(Request::class);

        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(true);
        $this->isConnectionsNumberLimitReachedQuery->method('execute')->willReturn(false);
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

    public function test_it_throws_access_denied_exception_when_the_custom_app_is_found_but_manage_apps_permission_is_missing(): void
    {
        $request = $this->createMock(Request::class);

        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(true);
        $this->isConnectionsNumberLimitReachedQuery->method('execute')->willReturn(false);
        $clientId = 'a_client_id';
        $app = App::fromCustomAppValues([
                    'id' => $clientId,
                    'name' => 'custom app',
                    'activate_url' => 'http://url.test',
                    'callback_url' => 'http://url.test',
                ]);
        $this->getAppQuery->method('execute')->with($clientId)->willReturn($app);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(false);
        $this->expectException(AccessDeniedHttpException::class);
        $this->sut->__invoke($request, $clientId);
    }
}
