<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Infrastructure\Marketplace;

use Akeneo\Connectivity\Connection\Application\Marketplace\WebMarketplaceAliasesInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceAliases;
use Akeneo\Platform\Bundle\PimVersionBundle\Version\FreeTrialVersion;
use Akeneo\Platform\Bundle\PimVersionBundle\Version\GrowthVersion;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebMarketplaceAliasesTest extends TestCase
{
    private VersionProviderInterface|MockObject $versionProvider;
    private WebMarketplaceAliases $sut;

    protected function setUp(): void
    {
        $this->versionProvider = $this->createMock(VersionProviderInterface::class);
        $this->sut = new WebMarketplaceAliases($this->versionProvider, new GrowthVersion(), new FreeTrialVersion());
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(WebMarketplaceAliases::class, $this->sut);
        $this->assertInstanceOf(WebMarketplaceAliasesInterface::class, $this->sut);
    }

    public function test_it_returns_the_utm_campaign_when_ge(): void
    {
        $this->versionProvider->method('getEdition')->willReturn('Growth Edition');
        $this->assertSame('connect_ge', $this->sut->getUtmCampaign());
    }

    public function test_it_returns_null_as_utm_campaign_when_unknown_edition(): void
    {
        $this->versionProvider->method('getEdition')->willReturn('Foo');
        $this->assertNull($this->sut->getUtmCampaign());
    }

    public function test_it_returns_the_edition_when_ge(): void
    {
        $this->versionProvider->method('getEdition')->willReturn('Growth Edition');
        $this->assertSame('growth-edition', $this->sut->getEdition());
    }

    public function test_it_returns_the_edition_when_free_trial(): void
    {
        $this->versionProvider->method('getEdition')->willReturn('Free Trial Edition');
        $this->assertSame('growth-edition', $this->sut->getEdition());
    }

    public function test_it_returns_the_edition_when_ce(): void
    {
        $this->versionProvider->method('getEdition')->willReturn('CE');
        $this->assertSame('community-edition', $this->sut->getEdition());
    }

    public function test_it_returns_the_ce_edition_by_default_when_unknown_edition(): void
    {
        $this->versionProvider->method('getEdition')->willReturn('Foo');
        $this->assertSame('community-edition', $this->sut->getEdition());
    }

    public function test_it_returns_the_version_when_semantic(): void
    {
        $this->versionProvider->method('getVersion')->willReturn('5.0.3');
        $this->assertSame('5.0', $this->sut->getVersion());
    }

    public function test_it_returns_null_when_unsupported_version(): void
    {
        $this->versionProvider->method('getVersion')->willReturn('20210713150654');
        $this->assertNull($this->sut->getVersion());
    }
}
