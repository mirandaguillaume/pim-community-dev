<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindAllConnectedAppsQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller\Internal\GetAllConnectedAppsAction;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetAllConnectedAppsActionTest extends TestCase
{
    private FeatureFlag|MockObject $featureFlag;
    private FindAllConnectedAppsQueryInterface|MockObject $findAllConnectedAppsQuery;
    private GetAllConnectedAppsAction $sut;

    protected function setUp(): void
    {
        $this->featureFlag = $this->createMock(FeatureFlag::class);
        $this->findAllConnectedAppsQuery = $this->createMock(FindAllConnectedAppsQueryInterface::class);
        $this->sut = new GetAllConnectedAppsAction(
            $this->featureFlag,
            $this->findAllConnectedAppsQuery,
        );
    }

    public function test_it_throws_not_found_exception_with_feature_flag_disabled(): void
    {
        $request = $this->createMock(Request::class);

        $this->featureFlag->method('isEnabled')->willReturn(false);
        $this->expectException(new NotFoundHttpException());
        $this->sut->__invoke($request, 'foo');
    }

    public function test_it_redirects_on_missing_xmlhttprequest_header(): void
    {
        $request = $this->createMock(Request::class);

        $this->featureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(false);
        $this->assertEquals(new RedirectResponse('/'), $this->sut->__invoke($request, 'foo'));
    }
}
