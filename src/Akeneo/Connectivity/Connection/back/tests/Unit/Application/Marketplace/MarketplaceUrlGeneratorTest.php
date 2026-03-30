<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Marketplace;

use Akeneo\Connectivity\Connection\Application\Marketplace\MarketplaceUrlGenerator;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetUserProfileQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\MarketplaceUrlGeneratorInterface;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MarketplaceUrlGeneratorTest extends TestCase
{
    private VersionProviderInterface|MockObject $versionProvider;
    private GetUserProfileQueryInterface|MockObject $getUserProfileQuery;
    private MarketplaceUrlGenerator $sut;

    protected function setUp(): void
    {
        $this->versionProvider = $this->createMock(VersionProviderInterface::class);
        $this->getUserProfileQuery = $this->createMock(GetUserProfileQueryInterface::class);
        $this->sut = new MarketplaceUrlGenerator(
            'https://marketplace.akeneo.test',
            $this->versionProvider,
            'http://my-akeneo.test',
            $this->getUserProfileQuery
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(MarketplaceUrlGenerator::class, $this->sut);
        $this->assertInstanceOf(MarketplaceUrlGeneratorInterface::class, $this->sut);
    }

    public function test_it_generates_an_url_to_the_serenity_marketplace(): void
    {
        $this->versionProvider->method('getEdition')->willReturn('Serenity');
        $this->getUserProfileQuery->method('execute')->with('willy')->willReturn('manager');
        $this->assertSame('https://marketplace.akeneo.test/?'
                        . 'utm_medium=pim&'
                        . 'utm_content=marketplace_button&'
                        . 'utm_source=http%3A%2F%2Fmy-akeneo.test&'
                        . 'utm_term=manager&'
                        . 'utm_campaign=connect_serenity', $this->sut->generateUrl('willy'));
    }

    public function test_it_generates_an_url_to_the_ge_marketplace(): void
    {
        $this->getUserProfileQuery->method('execute')->with('willy')->willReturn('developer');
        $this->versionProvider->method('getEdition')->willReturn('GE');
        $this->assertSame('https://marketplace.akeneo.test/?'
                        . 'utm_medium=pim&'
                        . 'utm_content=marketplace_button&'
                        . 'utm_source=http%3A%2F%2Fmy-akeneo.test&'
                        . 'utm_term=developer&'
                        . 'utm_campaign=connect_ge', $this->sut->generateUrl('willy'));
    }

    public function test_it_generates_a_default_url(): void
    {
        $this->getUserProfileQuery->method('execute')->with('willy')->willReturn(null);
        $this->versionProvider->method('getEdition')->willReturn('anything');
        $this->assertSame('https://marketplace.akeneo.test/?'
                        . 'utm_medium=pim&'
                        . 'utm_content=marketplace_button&'
                        . 'utm_source=http%3A%2F%2Fmy-akeneo.test', $this->sut->generateUrl('willy'));
    }

    public function test_it_throws_an_exception_if_the_market_place_url_is_not_an_url(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->expectExceptionMessage('$marketplaceUrl must be a valid URL.');
        new MarketplaceUrlGenerator('coucou', $this->versionProvider, 'http://my-akeneo.test', $this->getUserProfileQuery);
    }
}
