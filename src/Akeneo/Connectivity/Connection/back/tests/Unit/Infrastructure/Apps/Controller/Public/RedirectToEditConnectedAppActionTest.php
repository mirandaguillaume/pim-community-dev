<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Controller\Public;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByIdQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Public\RedirectToEditConnectedAppAction;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RedirectToEditConnectedAppActionTest extends TestCase
{
    private RouterInterface|MockObject $router;
    private SecurityFacade|MockObject $security;
    private FindOneConnectedAppByIdQueryInterface|MockObject $findOneConnectedAppByIdQuery;
    private RedirectToEditConnectedAppAction $sut;

    protected function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->security = $this->createMock(SecurityFacade::class);
        $this->findOneConnectedAppByIdQuery = $this->createMock(FindOneConnectedAppByIdQueryInterface::class);
        $this->sut = new RedirectToEditConnectedAppAction(
            $this->router,
            $this->security,
            $this->findOneConnectedAppByIdQuery,
        );
    }

    public function test_it_is_a_redirect_to_connected_app_editing_action(): void
    {
        $this->assertInstanceOf(RedirectToEditConnectedAppAction::class, $this->sut);
    }

    public function test_it_throws_not_found_exception_when_connected_app_is_not_found(): void
    {
        $badId = '00000000-0000-0000-0000-000000000000';
        $this->findOneConnectedAppByIdQuery->method('execute')->with($badId)->willReturn(null);
        $this->expectException(NotFoundHttpException::class);
        $this->sut->__invoke($badId);
    }

    public function test_it_denies_user_that_cannot_manage_a_custom_app(): void
    {
        $appId = '06416ae6-56e6-4a63-82af-522373fbf901';
        $this->findOneConnectedAppByIdQuery->method('execute')->with($appId)->willReturn(new ConnectedApp(
            $appId,
            'a_connected_app_name',
            ['read_scope_d', 'read_scope_b'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group',
            'an_username',
            [],
            false,
            null,
            true,
        ));
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(false);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_open_apps')->willReturn(true);
        $this->expectException(AccessDeniedHttpException::class);
        $this->sut->__invoke($appId);
    }

    public function test_it_denies_user_that_cannot_manage_an_app(): void
    {
        $appId = '06416ae6-56e6-4a63-82af-522373fbf901';
        $this->findOneConnectedAppByIdQuery->method('execute')->with($appId)->willReturn(new ConnectedApp(
            'a_connected_app_id',
            'a_connected_app_name',
            ['read_scope_d', 'read_scope_b'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group',
            'an_username',
            [],
            false,
            null,
            false,
        ));
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(false);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_open_apps')->willReturn(true);
        $this->expectException(AccessDeniedHttpException::class);
        $this->sut->__invoke($appId);
    }

    public function test_it_redirects_user_to_the_edit_connected_app_page(): void
    {
        $appId = '06416ae6-56e6-4a63-82af-522373fbf901';
        $this->findOneConnectedAppByIdQuery->method('execute')->with($appId)->willReturn(new ConnectedApp(
            'a_connected_app_id',
            'a_connected_app_name',
            ['read_scope_d', 'read_scope_b'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group',
            'an_username',
            [],
            false,
            null,
            false,
        ));
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_open_apps')->willReturn(true);
        $this->router->method('generate')->with('akeneo_connectivity_connection_connect_connected_apps_edit', [
                        'connectionCode' => 'random_connection_code',
                    ])->willReturn('/connect/connected-apps/random_connection_code');
        $this->assertEquals(new RedirectResponse('/#/connect/connected-apps/random_connection_code'), $this->sut->__invoke($appId));
    }
}
