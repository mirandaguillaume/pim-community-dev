<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Symfony\Controller\Dashboard;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetDashboardScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TimePeriod;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller\Dashboard\DashboardOverviewController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardOverviewControllerTest extends TestCase
{
    private GetDashboardScoresQueryInterface|MockObject $getDashboardScoresQuery;
    private DashboardOverviewController $sut;

    protected function setUp(): void
    {
        $this->getDashboardScoresQuery = $this->createMock(GetDashboardScoresQueryInterface::class);
        $this->sut = new DashboardOverviewController($this->getDashboardScoresQuery);
    }

    public function test_it_returns_a_http_bad_request_response_if_an_invalid_category_code_is_given(): void
    {
        $this->getDashboardScoresQuery->expects($this->never())->method('byCategory');
        $request = new Request(['category' => '']);
        $this->assertEquals(new JsonResponse(['error' => 'A category code cannot be empty'], Response::HTTP_BAD_REQUEST), $this->sut->__invoke($request, 'ecommerce', 'en_US', TimePeriod::DAILY));
    }

    public function test_it_returns_a_http_bad_request_response_if_an_invalid_family_code_is_given(): void
    {
        $this->getDashboardScoresQuery->expects($this->never())->method('byFamily');
        $request = new Request(['family' => '']);
        $this->assertEquals(new JsonResponse(['error' => 'A family code cannot be empty'], Response::HTTP_BAD_REQUEST), $this->sut->__invoke($request, 'ecommerce', 'en_US', TimePeriod::DAILY));
    }

    public function test_it_returns_an_empty_response_if_there_is_no_rates(): void
    {
        $getDashboardRatesQuery = $this->createMock(GetDashboardScoresQueryInterface::class);

        $getDashboardRatesQuery->method('byCatalog')->with(new ChannelCode('ecommerce'), new LocaleCode('en_US'), TimePeriod::daily())->willReturn(null);
        $this->assertEquals(new JsonResponse([]), $this->sut->__invoke(new Request(), 'ecommerce', 'en_US', TimePeriod::DAILY));
    }
}
