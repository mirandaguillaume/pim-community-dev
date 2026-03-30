<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Controller\Internal;

use Akeneo\Connectivity\Connection\Application\Marketplace\AppUrlGenerator;
use Akeneo\Connectivity\Connection\Application\Marketplace\MarketplaceAnalyticsGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllAppsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAllAppsQueryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use spec\Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Controller\Internal\GetAllAppsAction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetAllAppsActionTest extends TestCase
{
    private GetAllAppsQueryInterface|MockObject $getAllAppsQuery;
    private MarketplaceAnalyticsGenerator|MockObject $marketplaceAnalyticsGenerator;
    private UserContext|MockObject $userContext;
    private LoggerInterface|MockObject $logger;
    private FeatureFlag|MockObject $activateFeatureFlag;
    private GetAllAppsAction $sut;

    protected function setUp(): void
    {
        $this->getAllAppsQuery = $this->createMock(GetAllAppsQueryInterface::class);
        $this->marketplaceAnalyticsGenerator = $this->createMock(MarketplaceAnalyticsGenerator::class);
        $this->userContext = $this->createMock(UserContext::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->activateFeatureFlag = $this->createMock(FeatureFlag::class);
        $this->sut = new GetAllAppsAction(
            new AppUrlGenerator(new PimUrl('https://some_pim_url')),
            $this->getAllAppsQuery,
            $this->marketplaceAnalyticsGenerator,
            $this->userContext,
            $this->logger,
            $this->activateFeatureFlag,
        );
    }

    public function test_it_returns_an_empty_list_when_the_marketplace_api_throws_a_bad_request_error(): void
    {
        $request = $this->createMock(Request::class);

        $this->activateFeatureFlag->method('isEnabled')->willReturn(true);
        $request->method('isXmlHttpRequest')->willReturn(true);
        $this->getAllAppsQuery->method('execute')->willThrowException(new \Exception('error message', Response::HTTP_BAD_REQUEST));
        $result = $this->__invoke($request);
        Assert::assertEquals(Response::HTTP_OK, $result->getStatusCode());
        Assert::assertEquals(
            \json_encode(GetAllAppsResult::create(0, [])->normalize(), JSON_THROW_ON_ERROR),
            $result->getContent()
        );
    }
}
