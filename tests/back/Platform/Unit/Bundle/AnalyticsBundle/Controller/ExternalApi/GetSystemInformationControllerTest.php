<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Bundle\AnalyticsBundle\Controller\ExternalApi;

use Akeneo\Platform\Bundle\AnalyticsBundle\Controller\ExternalApi\GetSystemInformationController;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Akeneo\Platform\Bundle\PimVersionBundle\Version\GrowthVersion;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GetSystemInformationControllerTest extends TestCase
{
    public function test_it_is_a_system_information_controller(): void
    {
        $versionProvider = $this->createMock(VersionProviderInterface::class);

        $sut = new GetSystemInformationController($versionProvider, new GrowthVersion());
        $this->assertInstanceOf(GetSystemInformationController::class, $sut);
    }

    public function test_it_provides_system_information_for_community(): void
    {
        $versionProvider = $this->createMock(VersionProviderInterface::class);
        $request = $this->createMock(Request::class);

        $versionProvider->method('getVersion')->willReturn('12345678');
        $versionProvider->method('getEdition')->willReturn('CE');
        $sut = new GetSystemInformationController($versionProvider, new GrowthVersion());
        $response = $sut->__invoke($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(json_encode(
            [
                'version' => '12345678',
                'edition' => 'CE',
            ]
        ), $response->getContent());
    }

    public function test_it_provides_system_information_for_enterprise(): void
    {
        $versionProvider = $this->createMock(VersionProviderInterface::class);
        $request = $this->createMock(Request::class);

        $versionProvider->method('getVersion')->willReturn('12345678');
        $versionProvider->method('getEdition')->willReturn('EE');
        $sut = new GetSystemInformationController($versionProvider, new GrowthVersion());
        $response = $sut->__invoke($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(json_encode(
            [
                'version' => '12345678',
                'edition' => 'EE',
            ]
        ), $response->getContent());
    }

    public function test_it_provides_system_information_for_growthedition(): void
    {
        $versionProvider = $this->createMock(VersionProviderInterface::class);
        $request = $this->createMock(Request::class);

        $versionProvider->method('getVersion')->willReturn('12345678');
        $versionProvider->method('getEdition')->willReturn('Growth Edition');
        $sut = new GetSystemInformationController($versionProvider, new GrowthVersion());
        $response = $sut->__invoke($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(json_encode(
            [
                'version' => '12345678',
                'edition' => 'GE',
            ]
        ), $response->getContent());
    }
}
