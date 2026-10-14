<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Settings\Controller\Internal;

use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\IsConnectionsNumberLimitReachedQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Settings\Controller\Internal\IsConnectionsNumberLimitReachedAction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsConnectionsNumberLimitReachedActionTest extends TestCase
{
    private IsConnectionsNumberLimitReachedQueryInterface|MockObject $isConnectionsNumberLimitReachedQuery;
    private IsConnectionsNumberLimitReachedAction $sut;

    protected function setUp(): void
    {
        $this->isConnectionsNumberLimitReachedQuery = $this->createMock(IsConnectionsNumberLimitReachedQueryInterface::class);
        $this->sut = new IsConnectionsNumberLimitReachedAction($this->isConnectionsNumberLimitReachedQuery);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(IsConnectionsNumberLimitReachedAction::class, $this->sut);
    }

    public function test_it_redirects_on_missing_xmlhttprequest_header(): void
    {
        $request = $this->createMock(Request::class);

        $request->method('isXmlHttpRequest')->willReturn(false);
        $response = $this->sut->__invoke($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame(RedirectResponse::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function test_it_returns_limit_reached_flag(): void
    {
        $request = $this->createMock(Request::class);

        $request->method('isXmlHttpRequest')->willReturn(true);
        $this->isConnectionsNumberLimitReachedQuery->method('execute')->willReturn(true);
        $response = $this->sut->__invoke($request);

        $this->assertSame(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertSame(['limitReached' => true], \json_decode($response->getContent(), true));
    }
}
