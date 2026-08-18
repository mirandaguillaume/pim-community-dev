<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\CustomApps\Controller\Internal;

use Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence\IsCustomAppsNumberLimitReachedQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\Internal\IsCustomAppsNumberLimitReachedAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsCustomAppsNumberLimitReachedActionTest extends TestCase
{
    private IsCustomAppsNumberLimitReachedQueryInterface|MockObject $isCustomAppsNumberLimitReachedQuery;
    private IsCustomAppsNumberLimitReachedAction $sut;

    protected function setUp(): void
    {
        $this->isCustomAppsNumberLimitReachedQuery = $this->createMock(IsCustomAppsNumberLimitReachedQueryInterface::class);
        $this->sut = new IsCustomAppsNumberLimitReachedAction($this->isCustomAppsNumberLimitReachedQuery);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(IsCustomAppsNumberLimitReachedAction::class, $this->sut);
    }

    public function test_it_redirects_on_missing_xmlhttprequest_header(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('isXmlHttpRequest')->willReturn(false);

        $this->isCustomAppsNumberLimitReachedQuery->expects($this->never())->method('execute');

        $response = $this->sut->__invoke($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
    }

    public function test_it_returns_false_when_the_custom_apps_number_limit_is_not_reached(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('isXmlHttpRequest')->willReturn(true);

        $this->isCustomAppsNumberLimitReachedQuery->expects($this->once())->method('execute')->willReturn(false);

        $response = $this->sut->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('false', $response->getContent());
    }

    public function test_it_returns_true_when_the_custom_apps_number_limit_is_reached(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('isXmlHttpRequest')->willReturn(true);

        $this->isCustomAppsNumberLimitReachedQuery->expects($this->once())->method('execute')->willReturn(true);

        $response = $this->sut->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('true', $response->getContent());
    }
}
