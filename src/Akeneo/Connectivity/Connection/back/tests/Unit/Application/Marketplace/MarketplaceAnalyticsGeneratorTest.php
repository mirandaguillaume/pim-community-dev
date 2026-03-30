<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Marketplace;

use Akeneo\Connectivity\Connection\Application\Marketplace\MarketplaceAnalyticsGenerator;
use Akeneo\Connectivity\Connection\Application\Marketplace\WebMarketplaceAliasesInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetUserProfileQueryInterface;
use Akeneo\Platform\Bundle\FrameworkBundle\Service\PimUrl;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MarketplaceAnalyticsGeneratorTest extends TestCase
{
    private GetUserProfileQueryInterface|MockObject $getUserProfileQuery;
    private WebMarketplaceAliasesInterface|MockObject $webMarketplaceAliases;
    private PimUrl|MockObject $pimUrl;
    private MarketplaceAnalyticsGenerator $sut;

    protected function setUp(): void
    {
        $this->getUserProfileQuery = $this->createMock(GetUserProfileQueryInterface::class);
        $this->webMarketplaceAliases = $this->createMock(WebMarketplaceAliasesInterface::class);
        $this->pimUrl = $this->createMock(PimUrl::class);
        $this->sut = new MarketplaceAnalyticsGenerator(
            $this->getUserProfileQuery,
            $this->webMarketplaceAliases,
            $this->pimUrl
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(MarketplaceAnalyticsGenerator::class, $this->sut);
    }

    public function test_it_generates_extension_query_parameters_without_campaign_when_undefined(): void
    {
        $this->webMarketplaceAliases->method('getUtmCampaign')->willReturn(null);
        $this->pimUrl->method('getPimUrl')->willReturn('http://my-akeneo.test');
        $this->getUserProfileQuery->method('execute')->with('julia')->willReturn('manager');
        $this->assertSame([
                            'utm_medium' => 'pim',
                            'utm_content' => 'extension_link',
                            'utm_source' => 'http://my-akeneo.test',
                            'utm_term' => 'manager',
                        ], $this->sut->getExtensionQueryParameters('julia'));
    }

    public function test_it_generates_extension_query_parameters_for_the_growth_edition_environment(): void
    {
        $this->webMarketplaceAliases->method('getUtmCampaign')->willReturn('connect_ge');
        $this->pimUrl->method('getPimUrl')->willReturn('http://my-akeneo.test');
        $this->getUserProfileQuery->method('execute')->with('julia')->willReturn('manager');
        $this->assertSame([
                            'utm_medium' => 'pim',
                            'utm_content' => 'extension_link',
                            'utm_source' => 'http://my-akeneo.test',
                            'utm_term' => 'manager',
                            'utm_campaign' => 'connect_ge',
                        ], $this->sut->getExtensionQueryParameters('julia'));
    }

    public function test_it_generates_extension_query_parameters_without_profile_when_missing(): void
    {
        $this->webMarketplaceAliases->method('getUtmCampaign')->willReturn('connect_ge');
        $this->pimUrl->method('getPimUrl')->willReturn('http://my-akeneo.test');
        $this->getUserProfileQuery->method('execute')->with('julia')->willReturn(null);
        $this->assertSame([
                            'utm_medium' => 'pim',
                            'utm_content' => 'extension_link',
                            'utm_source' => 'http://my-akeneo.test',
                            'utm_campaign' => 'connect_ge',
                        ], $this->sut->getExtensionQueryParameters('julia'));
    }
}
