<?php

declare(strict_types=1);

namespace Akeneo\Test\Platform\Unit\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector\VersionDataCollector;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Akeneo\Platform\Installer\Infrastructure\InstallStatusManager\InstallStatusManager;
use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ServerBag;

class VersionDataCollectorTest extends TestCase
{
    private RequestStack|MockObject $requestStack;
    private VersionProviderInterface|MockObject $versionProvider;
    private InstallStatusManager|MockObject $installStatusManager;
    private FeatureFlags|MockObject $featureFlags;
    private VersionDataCollector $sut;

    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->versionProvider = $this->createMock(VersionProviderInterface::class);
        $this->installStatusManager = $this->createMock(InstallStatusManager::class);
        $this->featureFlags = $this->createMock(FeatureFlags::class);
        $this->sut = new VersionDataCollector(
            $this->requestStack,
            $this->versionProvider,
            $this->installStatusManager,
            'prod',
            $this->featureFlags,
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(VersionDataCollector::class, $this->sut);
        $this->assertInstanceOf(DataCollectorInterface::class, $this->sut);
    }

    public function test_it_collects_pim_version_edition_and_storage_driver(): void
    {
        $request = $this->createMock(Request::class);
        $serverBag = $this->createMock(ServerBag::class);

        $this->featureFlags->method('isEnabled')->with('reset_pim')->willReturn(true);
        $this->versionProvider->method('getPatch')->willReturn('1.4.0');
        $this->versionProvider->method('getEdition')->willReturn('CE');
        $this->requestStack->method('getCurrentRequest')->willReturn($request);
        $this->installStatusManager->method('getPimInstallDateTime')->willReturn(new \DateTime('2015-09-16T10:10:32+02:00'));
        $this->installStatusManager->method('getPimResetEvents')->willReturn([['time' => new \DateTimeImmutable('2015-09-17T10:10:32+02:00')]]);
        $request->server = $serverBag;
        $serverBag->method('get')->with('SERVER_SOFTWARE')->willReturn('Apache/2.4.12 (Debian)');
        $this->assertSame([
            'pim_edition'        => 'CE',
            'pim_version'        => '1.4.0',
            'pim_environment'    => 'prod',
            'pim_install_time'   => (new \DateTime('2015-09-16T10:10:32+02:00'))->format(\DateTime::ATOM),
            'server_version'     => 'Apache/2.4.12 (Debian)',
            'reset_event_count'  => 1,
            'last_reset_time'    => '2015-09-17T10:10:32+02:00',
        ], $this->sut->collect());
    }

    public function test_it_does_not_provides_server_version_of_pim_host_if_request_is_null(): void
    {
        $this->featureFlags->method('isEnabled')->with('reset_pim')->willReturn(false);
        $this->versionProvider->method('getPatch')->willReturn('1.4.0');
        $this->versionProvider->method('getEdition')->willReturn('CE');
        $this->requestStack->method('getCurrentRequest')->willReturn(null);
        $this->installStatusManager->method('getPimInstallDateTime')->willReturn(new \DateTime('2015-09-16T10:10:32+02:00'));
        $this->assertSame([
            'pim_edition'      => 'CE',
            'pim_version'      => '1.4.0',
            'pim_environment'  => 'prod',
            'pim_install_time' => (new \DateTime('2015-09-16T10:10:32+02:00'))->format(\DateTime::ATOM),
            'server_version'   => '',
        ], $this->sut->collect());
    }
}
