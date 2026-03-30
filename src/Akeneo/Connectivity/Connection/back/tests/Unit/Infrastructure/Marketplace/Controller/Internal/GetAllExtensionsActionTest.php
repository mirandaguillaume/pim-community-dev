<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Marketplace\MarketplaceAnalyticsGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllExtensionsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAllExtensionsQueryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Controller\Internal\GetAllExtensionsAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetAllExtensionsActionTest extends TestCase
{
    private GetAllExtensionsQueryInterface|MockObject $getAllExtensionsQuery;
    private MarketplaceAnalyticsGenerator|MockObject $marketplaceAnalyticsGenerator;
    private UserContext|MockObject $userContext;
    private LoggerInterface|MockObject $logger;
    private GetAllExtensionsAction $sut;

    protected function setUp(): void
    {
        $this->getAllExtensionsQuery = $this->createMock(GetAllExtensionsQueryInterface::class);
        $this->marketplaceAnalyticsGenerator = $this->createMock(MarketplaceAnalyticsGenerator::class);
        $this->userContext = $this->createMock(UserContext::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->sut = new GetAllExtensionsAction(
            $this->getAllExtensionsQuery,
            $this->marketplaceAnalyticsGenerator,
            $this->userContext,
            $this->logger,
        );
    }

    public function test_it_returns_an_empty_list_when_the_marketplace_api_throws_a_bad_request_error(): void
    {
        $request = $this->createMock(Request::class);

        $request->method('isXmlHttpRequest')->willReturn(true);
        $this->getAllExtensionsQuery->method('execute')->willThrowException(new \Exception('error message', Response::HTTP_BAD_REQUEST));
        $result = $this->__invoke($request);
        Assert::assertEquals(Response::HTTP_OK, $result->getStatusCode());
        Assert::assertEquals(
            \json_encode(GetAllExtensionsResult::create(0, [])->normalize(), JSON_THROW_ON_ERROR),
            $result->getContent()
        );
    }
}
