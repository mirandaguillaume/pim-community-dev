<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Marketplace\AppUrlGenerator;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByConnectionCodeQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\SaveConnectedAppOutdatedScopesFlagQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal\GetOpenAppUrlAction;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetOpenAppUrlActionTest extends TestCase
{
    private FeatureFlag|MockObject $marketplaceActivateFeatureFlag;
    private SecurityFacade|MockObject $security;
    private FindOneConnectedAppByConnectionCodeQueryInterface|MockObject $findOneConnectedAppByConnectionCodeQuery;
    private GetAppQueryInterface|MockObject $getAppQuery;
    private SaveConnectedAppOutdatedScopesFlagQueryInterface|MockObject $saveConnectedAppOutdatedScopesFlagQuery;
    private Request|MockObject $request;
    private GetOpenAppUrlAction $sut;

    protected function setUp(): void
    {
        $this->marketplaceActivateFeatureFlag = $this->createMock(FeatureFlag::class);
        $this->security = $this->createMock(SecurityFacade::class);
        $this->findOneConnectedAppByConnectionCodeQuery = $this->createMock(FindOneConnectedAppByConnectionCodeQueryInterface::class);
        $this->getAppQuery = $this->createMock(GetAppQueryInterface::class);
        $this->saveConnectedAppOutdatedScopesFlagQuery = $this->createMock(SaveConnectedAppOutdatedScopesFlagQueryInterface::class);
        $this->request = $this->createMock(Request::class);
        $this->sut = new GetOpenAppUrlAction(
            $this->marketplaceActivateFeatureFlag,
            $this->security,
            $this->findOneConnectedAppByConnectionCodeQuery,
            $this->getAppQuery,
            $this->saveConnectedAppOutdatedScopesFlagQuery,
            new AppUrlGenerator(new PimUrl('https://some_pim_url')),
        );
        $app = App::fromWebMarketplaceValues([
        'id' => 'connected_app_id',
        'name' => 'connected_app_name',
        'logo' => 'a/path/to/a/logo',
        'author' => 'author',
        'partner' => 'partner',
        'description' => 'a_description',
        'url' => 'https://marketplace.akeneo.com/app/connected_app_name',
        'categories' => [],
        'certified' => false,
        'activate_url' => 'http://app.example.com/activate',
        'callback_url' => 'http://app.example.com/callback',
        ]);
        $connectedApp = new ConnectedApp(
            'connected_app_id',
            'connected_app_name',
            ['some_scope'],
            'connection_code',
            'a/path/to/a/logo',
            'author',
            'group',
            'an_username',
            [],
            false,
            null,
            false,
            false
        );
        $this->request->method('isXmlHttpRequest')->willReturn(true);
        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(true);
        $this->findOneConnectedAppByConnectionCodeQuery->method('execute')->with('connection_code')->willReturn($connectedApp);
        $this->getAppQuery->method('execute')->with('connected_app_id')->willReturn($app);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(true);
    }

    public function test_it_redirects_on_missing_xmlhttprequest_header(): void
    {
        $this->request->method('isXmlHttpRequest')->willReturn(false);
        $this->assertEquals(new RedirectResponse('/'), $this->sut->__invoke($this->request, 'connection_code'));
    }

    public function test_it_throws_a_not_found_exception_when_feature_flag_is_disabled(): void
    {
        $this->marketplaceActivateFeatureFlag->method('isEnabled')->willReturn(false);
        $this->expectException(NotFoundHttpException::class);
        $this->sut->__invoke($this->request, 'connection_code');
    }

    public function test_it_throws_a_not_found_exception_when_a_non_connection_code_is_provided(): void
    {
        $this->findOneConnectedAppByConnectionCodeQuery->method('execute')->with('non_app_connection_code')->willReturn(null);
        $this->expectException(new NotFoundHttpException('Connected app with connection code non_app_connection_code does not exist.'));
        $this->sut->__invoke($this->request, 'non_app_connection_code');
    }

    public function test_it_expects_the_connected_app_to_have_its_app_store_counterpart(): void
    {
        $this->getAppQuery->method('execute')->with('connected_app_id')->willReturn(null);
        $this->expectException(new \LogicException('App not found with connected app id "connected_app_id"'));
        $this->sut->__invoke($this->request, 'connection_code');
    }

    public function test_it_denies_access_to_users_who_cannot_manage_or_open_apps(): void
    {
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_open_apps')->willReturn(false);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(false);
        $this->expectException(AccessDeniedHttpException::class);
        $this->sut->__invoke($this->request, 'connection_code');
    }

    public function test_it_denies_access_to_users_who_cannot_manage_custom_apps(): void
    {
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_open_apps')->willReturn(false);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(false);
        $this->getAppQuery->method('execute')->with('connected_app_id')->willReturn(App::fromCustomAppValues([
                    'id' => 'connected_app_id',
                    'name' => 'custom app',
                    'activate_url' => 'http://url.test',
                    'callback_url' => 'http://url.test',
                ]));
        $this->expectException(AccessDeniedHttpException::class);
        $this->sut->__invoke($this->request, 'connection_code');
    }

    public function test_it_clears_connected_app_from_outdated_scope_flag(): void
    {
        $this->findOneConnectedAppByConnectionCodeQuery->method('execute')->with('connection_code')->willReturn(new ConnectedApp(
            'connected_app_id',
            'connected_app_name',
            ['some_scope'],
            'connection_code',
            'a/path/to/a/logo',
            'author',
            'group',
            'an_username',
            [],
            false,
            null,
            false,
            false,
            true
        ));
        $this->sut->__invoke($this->request, 'connection_code');
        $this->saveConnectedAppOutdatedScopesFlagQuery->method('execute')->with('connected_app_id', false);
    }

    public function test_it_does_not_update_the_flag_if_connected_app_is_not_flagged(): void
    {
        $this->sut->__invoke($this->request, 'connection_code');
        $this->saveConnectedAppOutdatedScopesFlagQuery->expects($this->never())->method('execute')->with('connected_app_id', false);
    }

    public function test_it_does_not_update_the_flag_if_user_cannot_manage_apps(): void
    {
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_open_apps')->willReturn(true);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(false);
        $this->findOneConnectedAppByConnectionCodeQuery->method('execute')->with('connection_code')->willReturn(new ConnectedApp(
            'connected_app_id',
            'connected_app_name',
            ['some_scope'],
            'connection_code',
            'a/path/to/a/logo',
            'author',
            'group',
            'an_username',
            [],
            false,
            null,
            false,
            false,
            true
        ));
        $this->sut->__invoke($this->request, 'connection_code');
        $this->saveConnectedAppOutdatedScopesFlagQuery->expects($this->never())->method('execute')->with('connected_app_id', false);
    }

    public function test_it_returns_url_to_open_the_app_with_pim_url_within(): void
    {
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_open_apps')->willReturn(true);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(false);
        $this->assertEquals(new JsonResponse([
                    'url' => 'http://app.example.com/activate?pim_url=https%3A%2F%2Fsome_pim_url',
                ]), $this->sut->__invoke($this->request, 'connection_code'));
    }
}
