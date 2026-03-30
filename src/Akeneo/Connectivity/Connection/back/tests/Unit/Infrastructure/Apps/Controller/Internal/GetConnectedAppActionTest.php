<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByConnectionCodeQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal\GetConnectedAppAction;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetConnectedAppActionTest extends TestCase
{
    private FeatureFlag|MockObject $featureFlag;
    private SecurityFacade|MockObject $security;
    private FindOneConnectedAppByConnectionCodeQueryInterface|MockObject $findOneConnectedAppByConnectionCodeQuery;
    private GetConnectedAppAction $sut;

    protected function setUp(): void
    {
        $this->featureFlag = $this->createMock(FeatureFlag::class);
        $this->security = $this->createMock(SecurityFacade::class);
        $this->findOneConnectedAppByConnectionCodeQuery = $this->createMock(FindOneConnectedAppByConnectionCodeQueryInterface::class);
        $this->sut = new GetConnectedAppAction(
            $this->featureFlag,
            $this->security,
            $this->findOneConnectedAppByConnectionCodeQuery,
        );
    }

    public function test_it_throws_not_found_exception_with_feature_flag_disabled(): void
    {
        $request = $this->createMock(Request::class);

        $this->featureFlag->method('isEnabled')->willReturn(false);
        $this->expectException(NotFoundHttpException::class);
        $this->sut->__invoke($request, 'foo');
    }

    public function test_it_redirects_on_missing_xmlhttprequest_header(): void
    {
        $request = $this->createMock(Request::class);

        $this->featureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(false);
        $this->assertEquals(new RedirectResponse('/'), $this->sut->__invoke($request, 'foo'));
    }

    public function test_it_throws_access_denied_exception_with_missing_manage_apps_acl(): void
    {
        $request = $this->createMock(Request::class);

        $this->featureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(true);
        $connectedApp = new ConnectedApp(
            'a_connected_app_id',
            'a_connected_app_name',
            ['a_scope'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group',
            'an_username',
        );
        $this->findOneConnectedAppByConnectionCodeQuery->method('execute')->with('foo')->willReturn($connectedApp);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(false);
        $this->expectException(AccessDeniedHttpException::class);
        $this->sut->__invoke($request, 'foo');
    }

    public function test_it_throws_not_found_exception_with_wrong_connection_code(): void
    {
        $request = $this->createMock(Request::class);

        $this->featureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(true);
        $this->security->method('isGranted')->with('akeneo_connectivity_connection_manage_apps')->willReturn(true);
        $this->findOneConnectedAppByConnectionCodeQuery->method('execute')->with('foo')->willReturn(null);
        $this->expectException(NotFoundHttpException::class);

        $this->expectExceptionMessage('Connected app with connection code foo does not exist.');
        $this->sut->__invoke($request, 'foo');
    }
}
